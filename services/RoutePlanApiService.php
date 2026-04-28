<?php

namespace app\services;

use RuntimeException;
use Yii;
use yii\base\Component;

class RoutePlanApiService extends Component
{
    public function listRoutePlans(): array
    {
        $plans = Yii::$app->backendApi->getJson('/api/route-plans', $this->headers());
        return is_array($plans) ? $plans : [];
    }

    public function getRoutePlan(int $routePlanId): array
    {
        return Yii::$app->backendApi->getJson('/api/route-plans/' . $routePlanId, $this->headers());
    }

    public function createRoutePlan(array $payload): array
    {
        return Yii::$app->backendApi->postJson('/api/route-plans', $payload, $this->headers());
    }

    public function updateRoutePlan(int $routePlanId, array $payload): array
    {
        return Yii::$app->backendApi->patchJson('/api/route-plans/' . $routePlanId, $payload, $this->headers());
    }

    public function deleteRoutePlan(int $routePlanId): void
    {
        Yii::$app->backendApi->deleteJson('/api/route-plans/' . $routePlanId, $this->headers());
    }

    public function addTarget(int $routePlanId, int $targetId): array
    {
        return Yii::$app->backendApi->postJson(
            sprintf('/api/route-plans/%d/stops/targets/%d', $routePlanId, $targetId),
            [],
            $this->headers()
        );
    }

    public function addSpecies(int $routePlanId, int $speciesId): array
    {
        return Yii::$app->backendApi->postJson(
            sprintf('/api/route-plans/%d/stops/species/%d', $routePlanId, $speciesId),
            [],
            $this->headers()
        );
    }

    public function toggleObservationPoint(int $routePlanId, int $observationId): array
    {
        return Yii::$app->backendApi->postJson(
            sprintf('/api/route-plans/%d/stops/observations/%d/toggle', $routePlanId, $observationId),
            [],
            $this->headers()
        );
    }

    public function removePoint(int $routePlanPointId): array
    {
        return Yii::$app->backendApi->deleteJson('/api/route-plans/stops/' . $routePlanPointId, $this->headers());
    }

    private function headers(): array
    {
        $headers = Yii::$app->backendAuthSession->getAuthorizationHeaders();
        if (empty($headers)) {
            throw new RuntimeException('No backend access token is available for the current web session.');
        }

        return $headers;
    }
}
