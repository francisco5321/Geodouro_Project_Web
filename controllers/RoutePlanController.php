<?php

namespace app\controllers;

use app\models\RoutePlan;
use app\models\RoutePlanPoint;
use app\models\SavedVisitTarget;
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
            'startPointJson' => json_encode($plan->getStartPoint(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function actionCreate()
    {
        $plan = new RoutePlan();
        $plan->user_id = (int) Yii::$app->user->id;

        if ($plan->load(Yii::$app->request->post()) && $plan->save()) {
            Yii::$app->session->setFlash('success', 'Percurso criado com sucesso.');
            return $this->redirect(['route-plan/view', 'id' => $plan->route_plan_id]);
        }

        return $this->render('create', [
            'model' => $plan,
        ]);
    }

    public function actionUpdate(int $id)
    {
        $plan = $this->findOwnedPlan($id);

        if ($plan->load(Yii::$app->request->post()) && $plan->save()) {
            Yii::$app->session->setFlash('success', 'Percurso atualizado com sucesso.');
            return $this->redirect(['route-plan/view', 'id' => $plan->route_plan_id]);
        }

        return $this->render('update', [
            'model' => $plan,
        ]);
    }

    public function actionDelete(int $id)
    {
        $plan = $this->findOwnedPlan($id);
        $plan->delete();

        Yii::$app->session->setFlash('success', 'Percurso removido com sucesso.');
        return $this->redirect(['route-plan/index']);
    }

    public function actionAddTarget(int $id, int $targetId)
    {
        $plan = $this->findOwnedPlan($id);
        $target = SavedVisitTarget::findOne(['saved_visit_target_id' => $targetId, 'user_id' => Yii::$app->user->id]);
        if ($target === null) {
            throw new NotFoundHttpException('Alvo de visita nao encontrado.');
        }

        $this->attachTargetToPlan($plan, $target);

        return $this->redirect(['route-plan/view', 'id' => $plan->route_plan_id]);
    }

    public function actionAddSpecies(int $id, int $speciesId)
    {
        $plan = $this->findOwnedPlan($id);

        $target = SavedVisitTarget::findOne([
            'user_id' => Yii::$app->user->id,
            'plant_species_id' => $speciesId,
        ]);

        if ($target === null) {
            $target = new SavedVisitTarget([
                'user_id' => Yii::$app->user->id,
                'plant_species_id' => $speciesId,
            ]);
            if (!$target->save()) {
                Yii::$app->session->setFlash('success', 'Nao foi possivel guardar essa planta para o percurso.');
                return $this->redirect(['route-plan/view', 'id' => $plan->route_plan_id, 'speciesQ' => Yii::$app->request->post('speciesQ', '')]);
            }
        }

        $this->attachTargetToPlan($plan, $target);

        return $this->redirect(['route-plan/view', 'id' => $plan->route_plan_id, 'speciesQ' => Yii::$app->request->post('speciesQ', '')]);
    }

    public function actionToggleObservationPoint(int $id, int $observationId): array|Response
    {
        $plan = $this->findOwnedPlan($id);
        $observation = Observation::findOne(['observation_id' => $observationId]);
        if ($observation === null || !$observation->hasCoordinates()) {
            throw new NotFoundHttpException('Observacao nao encontrada.');
        }

        $target = SavedVisitTarget::findOne([
            'user_id' => Yii::$app->user->id,
            'observation_id' => $observationId,
        ]);

        if ($target === null) {
            $target = new SavedVisitTarget([
                'user_id' => Yii::$app->user->id,
                'observation_id' => $observationId,
            ]);
            if (!$target->save()) {
                return $this->jsonToggleResponse(false, false, 'Nao foi possivel guardar a observacao para este percurso.');
            }
        }

        $existingPoint = RoutePlanPoint::findOne([
            'route_plan_id' => $plan->route_plan_id,
            'saved_visit_target_id' => $target->saved_visit_target_id,
        ]);

        if ($existingPoint !== null) {
            $existingPoint->delete();
            $this->resequenceRoutePlan((int) $plan->route_plan_id);
            return $this->jsonToggleResponse(true, false, 'Observacao removida do percurso.');
        }

        $point = new RoutePlanPoint([
            'route_plan_id' => $plan->route_plan_id,
            'saved_visit_target_id' => $target->saved_visit_target_id,
            'visit_order' => $plan->getNextVisitOrder(),
        ]);

        if (!$point->save()) {
            return $this->jsonToggleResponse(false, false, 'Nao foi possivel adicionar a observacao ao percurso.');
        }

        return $this->jsonToggleResponse(true, true, 'Observacao adicionada ao percurso.');
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
        $point->delete();
        $this->resequenceRoutePlan($routePlanId);

        Yii::$app->session->setFlash('success', 'Ponto removido do percurso.');
        return $this->redirect(['route-plan/view', 'id' => $routePlanId]);
    }

    private function attachTargetToPlan(RoutePlan $plan, SavedVisitTarget $target): void
    {
        $existingPoint = RoutePlanPoint::findOne([
            'route_plan_id' => $plan->route_plan_id,
            'saved_visit_target_id' => $target->saved_visit_target_id,
        ]);

        if ($existingPoint !== null) {
            Yii::$app->session->setFlash('success', 'Esse alvo ja esta neste percurso.');
            return;
        }

        $point = new RoutePlanPoint([
            'route_plan_id' => $plan->route_plan_id,
            'saved_visit_target_id' => $target->saved_visit_target_id,
            'visit_order' => $plan->getNextVisitOrder(),
        ]);

        if ($point->save()) {
            Yii::$app->session->setFlash('success', 'Alvo adicionado ao percurso.');
        } else {
            Yii::$app->session->setFlash('success', 'Nao foi possivel adicionar esse alvo ao percurso.');
        }
    }

    private function resequenceRoutePlan(int $routePlanId): void
    {
        $remaining = RoutePlanPoint::find()
            ->where(['route_plan_id' => $routePlanId])
            ->orderBy(['visit_order' => SORT_ASC, 'route_plan_point_id' => SORT_ASC])
            ->all();

        $order = 1;
        foreach ($remaining as $item) {
            if ((int) $item->visit_order !== $order) {
                $item->visit_order = $order;
                $item->save(false, ['visit_order']);
            }
            $order++;
        }
    }

    private function jsonToggleResponse(bool $success, bool $inRoute, string $message): array|Response
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => $success,
                'inRoute' => $inRoute,
                'message' => $message,
            ];
        }

        Yii::$app->session->setFlash('success', $message);
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
