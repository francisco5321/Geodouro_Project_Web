<?php

namespace app\services;

use RuntimeException;
use Yii;
use yii\base\Component;

class RoutePlanApiService extends Component
{
    public function listRoutePlans(): array
    {
        $headers = Yii::$app->backendAuthSession->getAuthorizationHeaders();
        if (empty($headers)) {
            throw new RuntimeException('No backend access token is available for the current web session.');
        }

        $plans = Yii::$app->backendApi->getJson('/api/route-plans', $headers);
        return is_array($plans) ? $plans : [];
    }
}
