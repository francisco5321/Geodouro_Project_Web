<?php

namespace app\services;

use RuntimeException;
use Yii;
use yii\base\Component;

class VisitTargetApiService extends Component
{
    public function listVisitTargets(): array
    {
        $targets = Yii::$app->backendApi->getJson('/api/visit-targets', $this->headers());
        return is_array($targets) ? $targets : [];
    }

    public function toggle(string $targetType, int $targetId): array
    {
        return Yii::$app->backendApi->postJson('/api/visit-targets/toggle', [
            'targetType' => $targetType,
            'targetId' => $targetId,
        ], $this->headers());
    }

    public function remove(int $savedVisitTargetId): void
    {
        Yii::$app->backendApi->deleteJson('/api/visit-targets/' . $savedVisitTargetId, $this->headers());
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
