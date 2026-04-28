<?php

namespace app\services;

use Yii;
use yii\base\Component;

class ObservationApiService extends Component
{
    public function listObservations(string $queryText, string $status, bool $mineOnly, int $page, int $pageSize): array
    {
        $path = '/api/observations';
        if ($mineOnly && !Yii::$app->user->isGuest) {
            $path .= '?' . http_build_query(['userId' => Yii::$app->user->id]);
        }

        $response = Yii::$app->backendApi->getJson($path, $this->headers());
        $items = $this->extractList($response);
        $observations = array_map(
            static fn (array $item): ApiObservation => self::observationFromApi($item),
            array_filter($items, 'is_array')
        );

        $filteredObservations = $this->filterObservations($observations, $queryText, $status);
        usort($filteredObservations, static fn (ApiObservation $left, ApiObservation $right): int => strcmp((string) $right->observed_at, (string) $left->observed_at));
        $pagedObservations = array_slice($filteredObservations, max(0, $page) * $pageSize, $pageSize);

        return [
            'items' => $pagedObservations,
            'totalCount' => count($filteredObservations),
            'summary' => $this->buildSummary($observations),
        ];
    }

    public function getObservationById(int $observationId): ?ApiObservation
    {
        $response = Yii::$app->backendApi->getJson('/api/observations', $this->headers());
        foreach ($this->extractList($response) as $item) {
            if (!is_array($item) || (int) ($item['observationId'] ?? $item['observation_id'] ?? 0) !== $observationId) {
                continue;
            }

            $deviceObservationId = trim((string) ($item['deviceObservationId'] ?? $item['device_observation_id'] ?? ''));
            if ($deviceObservationId === '') {
                return self::observationFromApi($item);
            }

            $detail = Yii::$app->backendApi->getJson('/api/observations/' . rawurlencode($deviceObservationId), $this->headers());
            return self::observationFromApi($detail);
        }

        return null;
    }

    public function saveObservation(array $payload): array
    {
        return Yii::$app->backendApi->postJson('/api/observations', $payload, $this->headers());
    }

    public function deleteObservation(int $observationId): void
    {
        Yii::$app->backendApi->deleteJson('/api/observations/' . $observationId, $this->headers());
    }

    private function headers(): array
    {
        return Yii::$app->backendAuthSession->getAuthorizationHeaders();
    }

    private function extractList(array $response): array
    {
        foreach (['items', 'observations', 'data'] as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                return $response[$key];
            }
        }

        return array_is_list($response) ? $response : [];
    }

    private static function observationFromApi(array $item): ApiObservation
    {
        return ApiObservation::fromArray([
            ...$item,
            'observation_id' => $item['observationId'] ?? $item['observation_id'] ?? null,
            'device_observation_id' => $item['deviceObservationId'] ?? $item['device_observation_id'] ?? null,
            'user_id' => $item['userId'] ?? $item['user_id'] ?? null,
            'plant_species_id' => $item['plantSpeciesId'] ?? $item['plant_species_id'] ?? null,
            'image_uri' => $item['imagePath'] ?? $item['image_path'] ?? $item['storedImagePath'] ?? $item['stored_image_path'] ?? null,
            'observationImages' => $item['imagePaths'] ?? $item['image_paths'] ?? [],
            'observed_at' => self::formatInstant($item['observedAt'] ?? $item['observed_at'] ?? null),
            'predicted_scientific_name' => $item['predictedScientificName'] ?? $item['predicted_scientific_name'] ?? null,
            'enriched_scientific_name' => $item['scientificName'] ?? $item['scientific_name'] ?? null,
            'enriched_common_name' => $item['commonName'] ?? $item['common_name'] ?? null,
            'enriched_family' => $item['family'] ?? null,
            'enriched_wikipedia_url' => $item['wikipediaUrl'] ?? $item['wikipedia_url'] ?? null,
            'enriched_photo_url' => $item['photoUrl'] ?? $item['photo_url'] ?? null,
            'is_published' => $item['isPublished'] ?? $item['is_published'] ?? false,
            'sync_status' => $item['syncStatus'] ?? $item['sync_status'] ?? 'PENDING',
        ]);
    }

    private static function formatInstant(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : $value;
    }

    /**
     * @param ApiObservation[] $observations
     * @return ApiObservation[]
     */
    private function filterObservations(array $observations, string $queryText, string $status): array
    {
        $queryText = mb_strtolower(trim($queryText));

        return array_values(array_filter($observations, static function (ApiObservation $observation) use ($queryText, $status): bool {
            if ($status === 'PUBLISHED' && !$observation->is_published) {
                return false;
            }
            if ($status !== 'all' && $status !== 'PUBLISHED' && $observation->sync_status !== $status) {
                return false;
            }
            if ($queryText === '') {
                return true;
            }

            $haystack = mb_strtolower(implode(' ', array_filter([
                $observation->getResolvedCommonName(),
                $observation->getResolvedScientificName(),
                $observation->getResolvedFamily(),
                $observation->predicted_scientific_name,
            ])));

            return str_contains($haystack, $queryText);
        }));
    }

    /**
     * @param ApiObservation[] $observations
     */
    private function buildSummary(array $observations): array
    {
        $summary = ['total' => count($observations), 'published' => 0, 'pending' => 0, 'failed' => 0];
        foreach ($observations as $observation) {
            if ($observation->is_published) {
                $summary['published']++;
            }
            if ($observation->sync_status === 'PENDING') {
                $summary['pending']++;
            }
            if ($observation->sync_status === 'FAILED') {
                $summary['failed']++;
            }
        }

        return $summary;
    }
}
