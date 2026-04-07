<?php

namespace app\controllers;

use app\models\Observation;
use yii\web\Controller;

class MapController extends Controller
{
    public function actionIndex(): string
    {
        $observations = Observation::find()
            ->with(['user', 'plantSpecies'])
            ->where(['not', ['latitude' => null]])
            ->andWhere(['not', ['longitude' => null]])
            ->orderBy(['observed_at' => SORT_DESC])
            ->limit(250)
            ->all();

        $markers = array_map(static function (Observation $observation): array {
            return [
                'id' => $observation->observation_id,
                'title' => $observation->getResolvedCommonName() ?: 'Observacao botanica',
                'scientificName' => $observation->getResolvedScientificName() ?: 'Sem classificacao enriquecida',
                'status' => $observation->is_published ? 'Publicada' : $observation->sync_status,
                'latitude' => (float) $observation->latitude,
                'longitude' => (float) $observation->longitude,
                'detailUrl' => \yii\helpers\Url::to(['observation/view', 'id' => $observation->observation_id]),
                'speciesUrl' => $observation->plant_species_id ? \yii\helpers\Url::to(['species/view', 'id' => $observation->plant_species_id]) : null,
                'author' => $observation->user?->getFullName() ?? 'Sistema',
            ];
        }, $observations);

        return $this->render('index', [
            'observations' => $observations,
            'markersJson' => json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
