<?php

namespace app\services;

use Yii;
use yii\base\Component;

class PublicationApiService extends Component
{
    public function listPublications(string $scope, int $page, int $pageSize): array
    {
        $response = Yii::$app->backendApi->getJson('/api/publications', $this->headers());
        $publications = array_map(
            static fn (array $item): ApiPublication => ApiPublication::fromArray($item),
            array_filter($this->extractList($response), 'is_array')
        );

        if ($scope === 'mine' && !Yii::$app->user->isGuest) {
            $hasUserIds = false;
            foreach ($publications as $publication) {
                if ($publication->user_id !== null) {
                    $hasUserIds = true;
                    break;
                }
            }
            if ($hasUserIds) {
                $publications = array_values(array_filter(
                    $publications,
                    static fn (ApiPublication $publication): bool => (int) $publication->user_id === (int) Yii::$app->user->id
                ));
            }
        }

        usort($publications, static fn (ApiPublication $left, ApiPublication $right): int => strcmp((string) $right->published_at, (string) $left->published_at));

        return [
            'items' => array_slice($publications, max(0, $page) * $pageSize, $pageSize),
            'totalCount' => count($publications),
            'summary' => ['total' => count($publications)],
        ];
    }

    public function getPublicationById(int $publicationId): ?ApiPublication
    {
        return ApiPublication::fromArray(Yii::$app->backendApi->getJson('/api/publications/by-id/' . $publicationId, $this->headers()));
    }

    public function publishObservation(string $deviceObservationId, ?string $title, ?string $description): ApiPublication
    {
        return ApiPublication::fromArray(Yii::$app->backendApi->postJson('/api/publications', [
            'deviceObservationId' => $deviceObservationId,
            'title' => $title,
            'description' => $description,
        ], $this->headers()));
    }

    public function updatePublication(int $publicationId, array $payload): ApiPublication
    {
        return ApiPublication::fromArray(Yii::$app->backendApi->patchJson('/api/publications/' . $publicationId, $payload, $this->headers()));
    }

    public function deletePublication(int $publicationId): void
    {
        Yii::$app->backendApi->deleteJson('/api/publications/' . $publicationId, $this->headers());
    }

    private function headers(): array
    {
        return Yii::$app->backendAuthSession->getAuthorizationHeaders();
    }

    private function extractList(array $response): array
    {
        foreach (['items', 'publications', 'data'] as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                return $response[$key];
            }
        }

        return array_is_list($response) ? $response : [];
    }
}
