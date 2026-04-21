<?php

namespace app\controllers;

use app\models\Observation;
use app\models\SavedVisitTarget;
use yii\filters\AccessControl;
use yii\web\Controller;
use Yii;

class MapController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $observations = Observation::find()
            ->with(['user', 'plantSpecies', 'publication'])
            ->where(['not', ['latitude' => null]])
            ->andWhere(['not', ['longitude' => null]])
            ->orderBy(['observed_at' => SORT_DESC])
            ->limit(250)
            ->all();

        $visitTargets = Yii::$app->user->isGuest
            ? []
            : SavedVisitTarget::find()
                ->with(['publication', 'observation'])
                ->where(['user_id' => Yii::$app->user->id])
                ->all();

        $targetSpeciesIds = [];
        $targetObservationIds = [];
        foreach ($visitTargets as $target) {
            if ($target->plant_species_id !== null) {
                $targetSpeciesIds[] = (int) $target->plant_species_id;
            }
            if ($target->observation_id !== null) {
                $targetObservationIds[] = (int) $target->observation_id;
            }
            if ($target->publication?->observation_id !== null) {
                $targetObservationIds[] = (int) $target->publication->observation_id;
            }
        }
        $targetSpeciesIds = array_values(array_unique($targetSpeciesIds));
        $targetObservationIds = array_values(array_unique($targetObservationIds));

        $markers = array_map(static function (Observation $observation) use ($targetSpeciesIds, $targetObservationIds): array {
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
                'isVisitTarget' => in_array((int) $observation->plant_species_id, $targetSpeciesIds, true)
                    || in_array((int) $observation->observation_id, $targetObservationIds, true),
            ];
        }, $observations);

        return $this->render('index', [
            'observations' => $observations,
            'visitTargetCount' => count($visitTargets),
            'canCreateObservation' => Yii::$app->user->identity?->isAdmin() ?? false,
            'createObservationUrl' => \yii\helpers\Url::to(['observation/create']),
            'markersJson' => json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
}
