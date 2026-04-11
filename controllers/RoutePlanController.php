<?php

namespace app\controllers;

use app\models\RoutePlan;
use app\models\RoutePlanPoint;
use app\models\Observation;
use RuntimeException;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
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
        $plans = [];
        $pagination = null;
        $backendError = null;

        try {
            $plans = Yii::$app->routePlanApi->listRoutePlans();
        } catch (RuntimeException $exception) {
            $backendError = $exception->getMessage();
        }

        if ($backendError !== null) {
            $query = RoutePlan::find()
                ->with(['routePlanPoints.savedVisitTarget'])
                ->where(['user_id' => Yii::$app->user->id])
                ->orderBy(['updated_at' => SORT_DESC, 'route_plan_id' => SORT_DESC]);

            $pagination = new Pagination([
                'totalCount' => (clone $query)->count(),
                'pageSize' => 12,
            ]);

            $plans = $query
                ->offset($pagination->offset)
                ->limit($pagination->limit)
                ->all();
        }

        return $this->render('index', [
            'plans' => $plans,
            'pagination' => $pagination,
            'backendError' => $backendError,
        ]);
    }

    public function actionView(int $id): string
    {
        $plan = $this->findOwnedPlan($id);
        $plan->populateRelation('routePlanPoints', $plan->getRoutePlanPoints()->with(['savedVisitTarget.plantSpecies', 'savedVisitTarget.publication.plantSpecies', 'savedVisitTarget.publication.observation', 'savedVisitTarget.observation'])->all());
        $availableTargets = $plan->getPlannableTargets();
        $speciesSearch = trim((string) Yii::$app->request->get('speciesQ', ''));
        $plannableSpecies = $plan->getPlannableSpecies($speciesSearch);

        $markers = [];
        $routeCoordinates = [];
        $routeObservationIds = [];
        foreach ($plan->routePlanPoints as $point) {
            $target = $point->savedVisitTarget;
            $observation = $target?->getMapObservation();
            if ($observation === null || !$observation->hasCoordinates()) {
                continue;
            }

            $routeObservationIds[] = (int) $observation->observation_id;
            $marker = [
                'id' => $point->route_plan_point_id,
                'title' => $target->getTargetTitle(),
                'subtitle' => $target->getTargetSubtitle(),
                'latitude' => (float) $observation->latitude,
                'longitude' => (float) $observation->longitude,
                'order' => (int) $point->visit_order,
            ];
            $markers[] = $marker;
            $routeCoordinates[] = [$marker['latitude'], $marker['longitude']];
        }
        $routeObservationIds = array_values(array_unique($routeObservationIds));

        $backgroundObservations = Observation::find()
            ->with(['user', 'plantSpecies'])
            ->where(['not', ['latitude' => null]])
            ->andWhere(['not', ['longitude' => null]])
            ->orderBy(['observed_at' => SORT_DESC])
            ->limit(250)
            ->all();

        $backgroundMarkers = array_map(static function (Observation $observation) use ($routeObservationIds): array {
            return [
                'id' => $observation->observation_id,
                'latitude' => (float) $observation->latitude,
                'longitude' => (float) $observation->longitude,
                'title' => $observation->getResolvedCommonName() ?: 'Observacao botanica',
                'scientificName' => $observation->getResolvedScientificName() ?: 'Sem classificacao enriquecida',
                'status' => $observation->is_published ? 'Publicada' : $observation->sync_status,
                'detailUrl' => \yii\helpers\Url::to(['observation/view', 'id' => $observation->observation_id]),
                'isInRoute' => in_array((int) $observation->observation_id, $routeObservationIds, true),
            ];
        }, $backgroundObservations);

        return $this->render('view', [
            'plan' => $plan,
            'availableTargets' => $availableTargets,
            'plannableSpecies' => $plannableSpecies,
            'speciesSearch' => $speciesSearch,
            'markersJson' => json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'backgroundMarkersJson' => json_encode($backgroundMarkers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'routeCoordinatesJson' => json_encode($routeCoordinates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function actionCreate()
    {
        $plan = new RoutePlan();
        $plan->user_id = (int) Yii::$app->user->id;

        if ($plan->load(Yii::$app->request->post()) && $plan->validate()) {
            try {
                $response = Yii::$app->routePlanApi->createRoutePlan($this->routePlanPayload($plan));
                Yii::$app->session->setFlash('success', $response['message'] ?? 'Percurso criado com sucesso.');
                return $this->redirect(['route-plan/view', 'id' => (int) ($response['routePlanId'] ?? 0)]);
            } catch (RuntimeException $exception) {
                Yii::$app->session->setFlash('error', 'Nao foi possivel criar o percurso no backend comum: ' . $exception->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $plan,
        ]);
    }

    public function actionUpdate(int $id)
    {
        $plan = $this->findOwnedPlan($id);

        if ($plan->load(Yii::$app->request->post()) && $plan->validate()) {
            try {
                $response = Yii::$app->routePlanApi->updateRoutePlan((int) $plan->route_plan_id, $this->routePlanPayload($plan));
                Yii::$app->session->setFlash('success', $response['message'] ?? 'Percurso atualizado com sucesso.');
                return $this->redirect(['route-plan/view', 'id' => $plan->route_plan_id]);
            } catch (RuntimeException $exception) {
                Yii::$app->session->setFlash('error', 'Nao foi possivel atualizar o percurso no backend comum: ' . $exception->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $plan,
        ]);
    }

    public function actionDelete(int $id)
    {
        $plan = $this->findOwnedPlan($id);

        try {
            Yii::$app->routePlanApi->deleteRoutePlan((int) $plan->route_plan_id);
            Yii::$app->session->setFlash('success', 'Percurso removido com sucesso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel remover o percurso no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect(['route-plan/index']);
    }

    public function actionAddTarget(int $id, int $targetId)
    {
        $plan = $this->findOwnedPlan($id);

        try {
            $response = Yii::$app->routePlanApi->addTarget((int) $plan->route_plan_id, $targetId);
            Yii::$app->session->setFlash('success', $response['message'] ?? 'Alvo adicionado ao percurso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel adicionar o alvo no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect(['route-plan/view', 'id' => $plan->route_plan_id]);
    }

    public function actionAddSpecies(int $id, int $speciesId)
    {
        $plan = $this->findOwnedPlan($id);
        $speciesQ = Yii::$app->request->post('speciesQ', '');

        try {
            $response = Yii::$app->routePlanApi->addSpecies((int) $plan->route_plan_id, $speciesId);
            Yii::$app->session->setFlash('success', $response['message'] ?? 'Planta adicionada ao percurso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel adicionar a planta no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect(['route-plan/view', 'id' => $plan->route_plan_id, 'speciesQ' => $speciesQ]);
    }

    public function actionToggleObservationPoint(int $id, int $observationId): array|Response
    {
        $plan = $this->findOwnedPlan($id);

        try {
            $response = Yii::$app->routePlanApi->toggleObservationPoint((int) $plan->route_plan_id, $observationId);
            return $this->jsonToggleResponse(
                (bool) ($response['success'] ?? true),
                (bool) ($response['inRoute'] ?? false),
                $response['message'] ?? 'Percurso atualizado.'
            );
        } catch (RuntimeException $exception) {
            return $this->jsonToggleResponse(false, false, 'Nao foi possivel atualizar o percurso no backend comum: ' . $exception->getMessage(), true);
        }
    }

    public function actionRemovePoint(int $id)
    {
        $point = RoutePlanPoint::find()->with('routePlan')->where(['route_plan_point_id' => $id])->one();
        if ($point === null) {
            throw new NotFoundHttpException('Ponto do percurso nao encontrado.');
        }

        if (!$point->routePlan->canBeManagedBy(Yii::$app->user->identity)) {
            throw new ForbiddenHttpException('Nao tens permissao para alterar este percurso.');
        }

        $routePlanId = (int) $point->route_plan_id;
        try {
            $response = Yii::$app->routePlanApi->removePoint($id);
            Yii::$app->session->setFlash('success', $response['message'] ?? 'Ponto removido do percurso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel remover o ponto no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect(['route-plan/view', 'id' => $routePlanId]);
    }

    private function routePlanPayload(RoutePlan $plan): array
    {
        return [
            'name' => $plan->name,
            'description' => $plan->description,
            'startLabel' => null,
            'startLatitude' => null,
            'startLongitude' => null,
        ];
    }

    private function jsonToggleResponse(bool $success, bool $inRoute, string $message, bool $isError = false): array|Response
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => $success,
                'inRoute' => $inRoute,
                'message' => $message,
            ];
        }

        Yii::$app->session->setFlash($isError ? 'error' : 'success', $message);
        return $this->redirect(Yii::$app->request->referrer ?: ['route-plan/index']);
    }

    private function findOwnedPlan(int $id): RoutePlan
    {
        $plan = RoutePlan::findOne(['route_plan_id' => $id, 'user_id' => Yii::$app->user->id]);
        if ($plan === null) {
            throw new NotFoundHttpException('Percurso nao encontrado.');
        }

        return $plan;
    }
}
