<?php

namespace app\services;

use Yii;
use yii\base\Component;

class DashboardApiService extends Component
{
    public function getStats(): array
    {
        $response = Yii::$app->backendApi->getJson('/api/dashboard/stats');

        return [
            'speciesCount' => (int) ($response['speciesCount'] ?? $response['species_count'] ?? 0),
            'observationCount' => (int) ($response['observationCount'] ?? $response['observation_count'] ?? 0),
            'publicationCount' => (int) ($response['publicationCount'] ?? $response['publication_count'] ?? 0),
            'userCount' => (int) ($response['userCount'] ?? $response['user_count'] ?? 0),
        ];
    }
}
