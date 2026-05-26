<?php

namespace app\controllers;

use app\models\RoutePlanForm;
use app\services\ApiObservation;
use app\services\ApiPlantSpecies;
use RuntimeException;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RoutePlanController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'add-target' => ['post'],
                    'add-species' => ['post'],
                    'toggle-observation-point' => ['post'],
                    'remove-point' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $backendError = null;
        $plans = [];

        try {
            $plans = Yii::$app->routePlanApi->listRoutePlans();
        } catch (RuntimeException $exception) {
            $backendError = $exception->getMessage();
        }

        return $this->render('index', [
            'plans' => $plans,
            'pagination' => null,
            'backendError' => $backendError,
            'newPlan' => new RoutePlanForm(),
        ]);
    }

    public function actionView(int $id): string
    {
        try {
            $plan = Yii::$app->routePlanApi->getRoutePlan($id);
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException('Percurso não encontrado no backend.');
        }

        $stops = is_array($plan['stops'] ?? null) ? $plan['stops'] : [];
        $speciesSearch = trim((string) Yii::$app->request->get('speciesQ', ''));
        $routeObservationIds = [];
        $routeSpeciesIds = [];
        $markers = [];
        $routeCoordinates = [];

        foreach ($stops as $stop) {
            if (!is_array($stop)) {
                continue;
            }
            if (($stop['observationId'] ?? null) !== null) {
                $routeObservationIds[(int) $stop['observationId']] = true;
            }
            if (($stop['plantSpeciesId'] ?? null) !== null) {
                $routeSpeciesIds[(int) $stop['plantSpeciesId']] = true;
            }
            if (($stop['latitude'] ?? null) === null || ($stop['longitude'] ?? null) === null) {
                continue;
            }

            $marker = [
                'id' => (int) ($stop['routePlanPointId'] ?? 0),
                'title' => (string) ($stop['title'] ?? 'Paragem'),
                'subtitle' => (string) ($stop['subtitle'] ?? ''),
                'latitude' => (float) $stop['latitude'],
                'longitude' => (float) $stop['longitude'],
                'order' => (int) ($stop['visitOrder'] ?? 0),
            ];
            $markers[] = $marker;
            $routeCoordinates[] = [$marker['latitude'], $marker['longitude']];
        }

        $geometryPoints = is_array($plan['routeGeometry'] ?? null)
            ? array_values(array_filter($plan['routeGeometry'], static function ($point): bool {
                return is_array($point)
                    && isset($point['latitude'], $point['longitude'])
                    && is_numeric($point['latitude'])
                    && is_numeric($point['longitude']);
            }))
            : [];
        if ($geometryPoints !== []) {
            $routeCoordinates = array_map(
                static fn (array $point): array => [(float) $point['latitude'], (float) $point['longitude']],
                $geometryPoints
            );
        }

        try {
            $observationResult = Yii::$app->observationApi->listObservations('', 'all', false, 0, 250);
            $backgroundObservations = array_values(array_filter(
                $observationResult['items'],
                static fn (ApiObservation $observation): bool => $observation->hasCoordinates()
            ));
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $backgroundObservations = [];
        }

        $backgroundMarkers = array_map(static function (ApiObservation $observation) use ($routeObservationIds): array {
            return [
                'id' => $observation->observation_id,
                'latitude' => (float) $observation->latitude,
                'longitude' => (float) $observation->longitude,
                'title' => $observation->getResolvedCommonName() ?: 'Observação botânica',
                'scientificName' => $observation->getResolvedScientificName() ?: 'Sem classificação enriquecida',
                'status' => $observation->is_published ? 'Publicada' : $observation->sync_status,
                'detailUrl' => Url::to(['observation/view', 'id' => $observation->observation_id]),
                'isInRoute' => isset($routeObservationIds[(int) $observation->observation_id]),
            ];
        }, $backgroundObservations);

        try {
            $speciesResult = Yii::$app->speciesApi->listSpecies($speciesSearch, 'species', 0, 12);
            $plannableSpecies = array_values(array_filter(
                $speciesResult['items'],
                static fn (ApiPlantSpecies $species): bool => !isset($routeSpeciesIds[(int) $species->plant_species_id])
            ));
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $plannableSpecies = [];
        }

        return $this->render('view', [
            'plan' => $plan,
            'stops' => $stops,
            'availableTargets' => [],
            'plannableSpecies' => $plannableSpecies,
            'speciesSearch' => $speciesSearch,
            'markersJson' => json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'backgroundMarkersJson' => json_encode($backgroundMarkers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'routeCoordinatesJson' => json_encode($routeCoordinates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function actionCreate()
    {
        $plan = new RoutePlanForm();
        $plan->user_id = (int) Yii::$app->user->id;
        $plan->isNewRecord = true;

        if ($plan->load(Yii::$app->request->post()) && $plan->validate()) {
            try {
                $response = Yii::$app->routePlanApi->createRoutePlan($this->routePlanPayloadFromModel($plan));
                Yii::$app->session->setFlash('success', $response['message'] ?? 'Percurso criado com sucesso.');
                $routePlanId = (int) ($response['routePlanId'] ?? 0);
                return $routePlanId > 0 ? $this->redirect(['route-plan/view', 'id' => $routePlanId]) : $this->redirect(['route-plan/index']);
            } catch (RuntimeException $exception) {
                Yii::$app->session->setFlash('error', 'Não foi possível criar o percurso no backend comum: ' . $exception->getMessage());
            }
        }

        return $this->render('create', ['model' => $plan]);
    }

    public function actionUpdate(int $id)
    {
        $plan = $this->routePlanModelFromApi($id);

        if ($plan->load(Yii::$app->request->post()) && $plan->validate()) {
            try {
                $response = Yii::$app->routePlanApi->updateRoutePlan($id, $this->routePlanPayloadFromModel($plan));
                Yii::$app->session->setFlash('success', $response['message'] ?? 'Percurso atualizado com sucesso.');
                return $this->redirect(['route-plan/view', 'id' => $id]);
            } catch (RuntimeException $exception) {
                Yii::$app->session->setFlash('error', 'Não foi possível atualizar o percurso no backend comum: ' . $exception->getMessage());
            }
        }

        return $this->render('update', ['model' => $plan]);
    }

    public function actionDelete(int $id)
    {
        try {
            Yii::$app->routePlanApi->deleteRoutePlan($id);
            Yii::$app->session->setFlash('success', 'Percurso removido com sucesso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Não foi possível remover o percurso no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect(['route-plan/index']);
    }

    public function actionAddTarget(int $id, int $targetId)
    {
        try {
            $response = Yii::$app->routePlanApi->addTarget($id, $targetId);
            Yii::$app->session->setFlash('success', $response['message'] ?? 'Alvo adicionado ao percurso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Não foi possível adicionar o alvo no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect(['route-plan/view', 'id' => $id]);
    }

    public function actionAddSpecies(int $id, int $speciesId)
    {
        $speciesQ = Yii::$app->request->post('speciesQ', '');
        try {
            $response = Yii::$app->routePlanApi->addSpecies($id, $speciesId);
            Yii::$app->session->setFlash('success', $response['message'] ?? 'Planta adicionada ao percurso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Não foi possível adicionar a planta no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect(['route-plan/view', 'id' => $id, 'speciesQ' => $speciesQ]);
    }

    public function actionToggleObservationPoint(int $id, int $observationId): array|Response
    {
        try {
            $response = Yii::$app->routePlanApi->toggleObservationPoint($id, $observationId);
            return $this->jsonToggleResponse((bool) ($response['success'] ?? true), (bool) ($response['inRoute'] ?? false), $response['message'] ?? 'Percurso atualizado.');
        } catch (RuntimeException $exception) {
            return $this->jsonToggleResponse(false, false, 'Não foi possível atualizar o percurso no backend comum: ' . $exception->getMessage(), true);
        }
    }

    public function actionRemovePoint(int $id)
    {
        $routePlanId = (int) Yii::$app->request->post('routePlanId', 0);
        try {
            $response = Yii::$app->routePlanApi->removePoint($id);
            $routePlanId = (int) ($response['routePlanId'] ?? $routePlanId);
            Yii::$app->session->setFlash('success', $response['message'] ?? 'Ponto removido do percurso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Não foi possível remover o ponto no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect($routePlanId > 0 ? ['route-plan/view', 'id' => $routePlanId] : ['route-plan/index']);
    }

    private function routePlanPayloadFromModel(RoutePlanForm $plan): array
    {
        return [
            'name' => $plan->name,
            'description' => $plan->description,
            'startLabel' => $plan->start_label,
            'startLatitude' => $plan->start_latitude,
            'startLongitude' => $plan->start_longitude,
        ];
    }

    private function routePlanModelFromApi(int $id): RoutePlanForm
    {
        try {
            $data = Yii::$app->routePlanApi->getRoutePlan($id);
        } catch (RuntimeException $exception) {
            throw new NotFoundHttpException('Percurso não encontrado.');
        }

        $plan = new RoutePlanForm();
        $plan->route_plan_id = $id;
        $plan->user_id = (int) Yii::$app->user->id;
        $plan->name = (string) ($data['name'] ?? '');
        $plan->description = $data['description'] ?? null;
        $plan->start_label = isset($data['startLabel']) ? (string) $data['startLabel'] : null;
        $plan->start_latitude = isset($data['startLatitude']) && $data['startLatitude'] !== null ? (float) $data['startLatitude'] : null;
        $plan->start_longitude = isset($data['startLongitude']) && $data['startLongitude'] !== null ? (float) $data['startLongitude'] : null;
        $plan->isNewRecord = false;

        return $plan;
    }

    private function jsonToggleResponse(bool $success, bool $inRoute, string $message, bool $isError = false): array|Response
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => $success, 'inRoute' => $inRoute, 'message' => $message];
        }

        Yii::$app->session->setFlash($isError ? 'error' : 'success', $message);
        return $this->redirect(Yii::$app->request->referrer ?: ['route-plan/index']);
    }
}
