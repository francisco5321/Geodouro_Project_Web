<?php

namespace app\services;

use Yii;
use yii\base\Component;

class SpeciesApiService extends Component
{
    public function listSpecies(string $queryText, string $sort, int $page, int $pageSize): array
    {
        $response = Yii::$app->backendApi->getJson('/api/species', $this->headers());

        $items = $this->extractList($response, ['items', 'species', 'data']);
        $allSpecies = array_map(
            static fn (array $item): ApiPlantSpecies => ApiPlantSpecies::fromArray($item),
            array_filter($items, 'is_array')
        );
        $filteredSpecies = $this->filterSpecies($allSpecies, $queryText);
        $this->sortSpecies($filteredSpecies, $sort);
        $pagedSpecies = array_slice($filteredSpecies, max(0, $page) * $pageSize, $pageSize);

        return [
            'items' => $pagedSpecies,
            'totalCount' => count($filteredSpecies),
            'summary' => $this->buildSummary($allSpecies),
            'speciesImageMap' => $this->buildSpeciesImageMap($pagedSpecies, $items),
        ];
    }

    public function getSpecies(int $speciesId, int $page, int $pageSize): array
    {
        $speciesSlug = $this->findSpeciesSlug($speciesId);
        $response = Yii::$app->backendApi->getJson('/api/species/' . rawurlencode($speciesSlug), $this->headers());

        $species = isset($response['species']) && is_array($response['species'])
            ? $response['species']
            : $response;
        $observations = $this->extractList($response, ['observations', 'items', 'data']);
        $apiObservations = array_map(
            static fn (array $item): ApiObservation => ApiObservation::fromArray([
                ...$item,
                'image_uri' => $item['imagePath'] ?? $item['image_path'] ?? null,
                'observed_at' => isset($item['capturedAt']) && is_numeric($item['capturedAt']) ? date('Y-m-d H:i:s', (int) $item['capturedAt']) : null,
                'enriched_common_name' => $item['commonName'] ?? $item['common_name'] ?? null,
                'enriched_scientific_name' => $item['scientificName'] ?? $item['scientific_name'] ?? null,
            ]),
            array_filter($observations, 'is_array')
        );
        $pagedObservations = array_slice($apiObservations, max(0, $page) * $pageSize, $pageSize);

        return [
            'species' => is_array($species) ? ApiPlantSpecies::fromArray($species) : null,
            'observations' => $pagedObservations,
            'totalCount' => count($apiObservations),
            'galleryImages' => $this->buildGalleryImages($response),
            'locationSummary' => $response['locationSummary'] ?? null,
            'stats' => $this->normalizeStats($response['stats'] ?? $response),
        ];
    }

    private function findSpeciesSlug(int $speciesId): string
    {
        $response = Yii::$app->backendApi->getJson('/api/species', $this->headers());
        foreach ($this->extractList($response, ['items', 'species', 'data']) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $species = ApiPlantSpecies::fromArray($item);
            $candidateId = $item['plantSpeciesId'] ?? $item['plant_species_id'] ?? $item['id'] ?? null;
            if ($candidateId !== null && (int) $candidateId === $speciesId) {
                $slug = trim((string) ($item['slug'] ?? $item['species_id'] ?? $species->species_id ?? ''));
                if ($slug !== '') {
                    return $slug;
                }
            }

            if ((int) $species->plant_species_id === $speciesId && $species->species_id !== null) {
                return $species->species_id;
            }
        }

        return (string) $speciesId;
    }

    private function headers(): array
    {
        return Yii::$app->backendAuthSession->getAuthorizationHeaders();
    }

    private function extractList(array $response, array $keys): array
    {
        foreach ($keys as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                return $response[$key];
            }
        }

        return array_is_list($response) ? $response : [];
    }

    private function extractTotalCount(array $response, int $fallback): int
    {
        foreach (['totalCount', 'total', 'count'] as $key) {
            if (isset($response[$key]) && is_numeric($response[$key])) {
                return (int) $response[$key];
            }
        }

        if (isset($response['pagination']) && is_array($response['pagination'])) {
            return $this->extractTotalCount($response['pagination'], $fallback);
        }

        return $fallback;
    }

    private function normalizeSummary(array $summary): array
    {
        return [
            'speciesCount' => (int) ($summary['speciesCount'] ?? $summary['species_count'] ?? 0),
            'observationsCount' => (int) ($summary['observationsCount'] ?? $summary['observations_count'] ?? 0),
            'familiesCount' => (int) ($summary['familiesCount'] ?? $summary['families_count'] ?? 0),
        ];
    }

    /**
     * @param ApiPlantSpecies[] $species
     */
    private function buildSummary(array $species): array
    {
        $families = [];
        $observationsCount = 0;

        foreach ($species as $item) {
            if ($item->family !== null) {
                $families[$item->family] = true;
            }
            $observationsCount += $item->image_count;
        }

        return [
            'speciesCount' => count($species),
            'observationsCount' => $observationsCount,
            'familiesCount' => count($families),
        ];
    }

    /**
     * @param ApiPlantSpecies[] $species
     * @param array<int, mixed> $rawItems
     */
    private function buildSpeciesImageMap(array $species, array $rawItems): array
    {
        $pathsById = [];
        foreach ($rawItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $speciesId = (int) ($item['plantSpeciesId'] ?? $item['plant_species_id'] ?? 0);
            $path = trim((string) ($item['thumbnailPath'] ?? $item['thumbnail_path'] ?? ''));
            if ($speciesId > 0 && $path !== '') {
                $pathsById[$speciesId] = $path;
            }
        }

        $map = [];
        foreach ($species as $item) {
            $speciesId = (int) $item->plant_species_id;
            if (isset($pathsById[$speciesId])) {
                $map[$speciesId] = ['path' => $pathsById[$speciesId]];
            }
        }

        return $map;
    }

    /**
     * @param ApiPlantSpecies[] $species
     * @return ApiPlantSpecies[]
     */
    private function filterSpecies(array $species, string $queryText): array
    {
        $queryText = mb_strtolower(trim($queryText));
        if ($queryText === '') {
            return $species;
        }

        return array_values(array_filter($species, static function (ApiPlantSpecies $item) use ($queryText): bool {
            $haystack = mb_strtolower(implode(' ', array_filter([
                $item->scientific_name,
                $item->common_name,
                $item->family,
                $item->genus,
            ])));

            return str_contains($haystack, $queryText);
        }));
    }

    /**
     * @param ApiPlantSpecies[] $species
     */
    private function sortSpecies(array &$species, string $sort): void
    {
        usort($species, static function (ApiPlantSpecies $left, ApiPlantSpecies $right) use ($sort): int {
            return match ($sort) {
                'family' => [strtolower((string) $left->family), strtolower((string) $left->scientific_name)]
                    <=> [strtolower((string) $right->family), strtolower((string) $right->scientific_name)],
                'genus' => [strtolower((string) $left->genus), strtolower((string) $left->scientific_name)]
                    <=> [strtolower((string) $right->genus), strtolower((string) $right->scientific_name)],
                default => strtolower((string) $left->scientific_name) <=> strtolower((string) $right->scientific_name),
            };
        });
    }

    private function normalizeStats(array $stats): array
    {
        return [
            'observationsCount' => (int) ($stats['observationsCount'] ?? $stats['observations_count'] ?? $stats['observationCount'] ?? 0),
            'publishedCount' => (int) ($stats['publishedCount'] ?? $stats['published_count'] ?? 0),
            'syncedCount' => (int) ($stats['syncedCount'] ?? $stats['synced_count'] ?? 0),
            'avgConfidence' => isset($stats['avgConfidence']) ? (float) $stats['avgConfidence'] : ($stats['avg_confidence'] ?? null),
        ];
    }

    private function normalizeImageMap(array $imageMap): array
    {
        $normalized = [];
        foreach ($imageMap as $speciesId => $image) {
            if (!is_array($image)) {
                continue;
            }
            $normalized[(int) $speciesId] = $this->normalizeImage($image);
        }

        return $normalized;
    }

    private function normalizeImageList(array $images): array
    {
        return array_values(array_map(
            fn (array $image): array => $this->normalizeImage($image),
            array_filter($images, 'is_array')
        ));
    }

    private function normalizeImage(array $image): array
    {
        if (isset($image['path'])) {
            return ['path' => (string) $image['path']];
        }

        return [
            'observationId' => (int) ($image['observationId'] ?? $image['observation_id'] ?? 0),
            'imageIndex' => (int) ($image['imageIndex'] ?? $image['image_index'] ?? $image['index'] ?? 0),
        ];
    }

    private function buildGalleryImages(array $response): array
    {
        $paths = $response['galleryImagePaths'] ?? $response['gallery_image_paths'] ?? [];
        if (is_array($paths) && $paths !== []) {
            return array_values(array_map(
                static fn (string $path): array => ['path' => $path],
                array_filter($paths, static fn (mixed $path): bool => trim((string) $path) !== '')
            ));
        }

        $heroImagePath = trim((string) ($response['heroImagePath'] ?? $response['hero_image_path'] ?? ''));
        return $heroImagePath !== '' ? [['path' => $heroImagePath]] : [];
    }
}
