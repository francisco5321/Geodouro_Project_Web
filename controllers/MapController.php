<?php

namespace app\controllers;

use app\services\ApiObservation;
use RuntimeException;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

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
        $focusObservationId = (int) Yii::$app->request->get('observationId', 0);
        $totalObservationCount = 0;

        try {
            $totalObservationCount = (int) (Yii::$app->dashboardApi->getStats()['observationCount'] ?? 0);
            $result = Yii::$app->observationApi->listObservations('', 'all', false, 0, 250);
            $observations = array_values(array_filter(
                $result['items'],
                static fn (ApiObservation $observation): bool => $observation->hasCoordinates()
            ));
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $observations = [];
        }

        $hasFocusedObservation = false;
        foreach ($observations as $observation) {
            if ((int) $observation->observation_id === $focusObservationId) {
                $hasFocusedObservation = true;
                break;
            }
        }

        if ($focusObservationId > 0 && !$hasFocusedObservation) {
            try {
                $focusedObservation = Yii::$app->observationApi->getObservationById($focusObservationId);
                if ($focusedObservation?->hasCoordinates()) {
                    array_unshift($observations, $focusedObservation);
                }
            } catch (RuntimeException $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
            }
        }

        $visitTargets = [];
        if (!Yii::$app->user->isGuest) {
            try {
                $visitTargets = Yii::$app->visitTargetApi->listVisitTargets();
            } catch (RuntimeException $exception) {
                Yii::error($exception->getMessage(), __METHOD__);
            }
        }

        $targetSpeciesIds = [];
        $targetObservationIds = [];
        foreach ($visitTargets as $target) {
            if (!is_array($target)) {
                continue;
            }
            $plantSpeciesId = $target['plantSpeciesId'] ?? $target['plant_species_id'] ?? null;
            $observationId = $target['observationId'] ?? $target['observation_id'] ?? null;
            if ($plantSpeciesId !== null) {
                $targetSpeciesIds[(int) $plantSpeciesId] = true;
            }
            if ($observationId !== null) {
                $targetObservationIds[(int) $observationId] = true;
            }
        }

        $markers = array_map(static function (ApiObservation $observation) use ($targetSpeciesIds, $targetObservationIds): array {
            return [
                'id' => $observation->observation_id,
                'title' => $observation->getResolvedCommonName() ?: 'Observação botanica',
                'scientificName' => $observation->getResolvedScientificName() ?: 'Sem classificação enriquecida',
                'status' => $observation->is_published ? 'Publicada' : $observation->sync_status,
                'latitude' => (float) $observation->latitude,
                'longitude' => (float) $observation->longitude,
                'detailUrl' => \yii\helpers\Url::to(['observation/view', 'id' => $observation->observation_id]),
                'speciesUrl' => $observation->plant_species_id ? \yii\helpers\Url::to(['species/view', 'id' => $observation->plant_species_id]) : null,
                'isVisitTarget' => isset($targetSpeciesIds[(int) $observation->plant_species_id])
                    || isset($targetObservationIds[(int) $observation->observation_id]),
            ];
        }, $observations);

        return $this->render('index', [
            'observations' => $observations,
            'totalObservationCount' => $totalObservationCount,
            'visitTargetCount' => count($visitTargets),
            'canCreateObservation' => Yii::$app->user->identity?->isAdmin() ?? false,
            'createObservationUrl' => \yii\helpers\Url::to(['observation/create']),
            'markersJson' => json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'focusObservationId' => $focusObservationId,
        ]);
    }
}
