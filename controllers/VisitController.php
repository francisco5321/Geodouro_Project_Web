<?php

namespace app\controllers;

use app\models\Observation;
use app\models\PlantSpecies;
use app\models\Publication;
use app\models\SavedVisitTarget;
use app\models\RoutePlan;
use app\models\RoutePlanPoint;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class VisitController extends Controller
{
    private const CONSUMED_VISIT_TARGET_NOTE = '__geodouro_route_consumed__';
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
                    'toggle-species' => ['post'],
                    'toggle-publication' => ['post'],
                    'toggle-observation' => ['post'],
                    'create-route' => ['post'],
                    'remove' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $targets = SavedVisitTarget::find()
            ->with(['plantSpecies', 'publication.plantSpecies', 'publication.observation', 'observation.plantSpecies'])
            ->where(['user_id' => Yii::$app->user->id])
            ->andWhere(['or', ['notes' => null], ['<>', 'notes', self::CONSUMED_VISIT_TARGET_NOTE]])
            ->orderBy(['created_at' => SORT_DESC, 'saved_visit_target_id' => SORT_DESC])
            ->all();

        $plans = RoutePlan::find()
            ->where(['user_id' => Yii::$app->user->id])
            ->orderBy(['updated_at' => SORT_DESC, 'route_plan_id' => SORT_DESC])
            ->all();

        $savedObservationIds = [];
        foreach ($targets as $target) {
            $observation = $target->getMapObservation();
            if ($observation !== null) {
                $savedObservationIds[] = (int) $observation->observation_id;
            }
        }
        $savedObservationIds = array_values(array_unique($savedObservationIds));

        $observations = Observation::find()
            ->with(['user', 'plantSpecies', 'publication'])
            ->where(['not', ['latitude' => null]])
            ->andWhere(['not', ['longitude' => null]])
            ->orderBy(['observed_at' => SORT_DESC, 'observation_id' => SORT_DESC])
            ->limit(300)
            ->all();

        $markers = array_map(static function (Observation $observation) use ($savedObservationIds): array {
            return [
                'id' => $observation->observation_id,
                'title' => $observation->getResolvedCommonName() ?: 'Observação botânica',
                'scientificName' => $observation->getResolvedScientificName() ?: 'Sem classificação enriquecida',
                'status' => $observation->is_published ? 'Publicada' : $observation->sync_status,
                'latitude' => (float) $observation->latitude,
                'longitude' => (float) $observation->longitude,
                'detailUrl' => \yii\helpers\Url::to(['observation/view', 'id' => $observation->observation_id]),
                'isSaved' => in_array((int) $observation->observation_id, $savedObservationIds, true),
            ];
        }, $observations);

        return $this->render('index', [
            'targets' => $targets,
            'plans' => $plans,
            'markersJson' => json_encode($markers, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'newPlan' => new RoutePlan(),
        ]);
    }

    public function actionToggleSpecies(int $id)
    {
        $species = PlantSpecies::findOne(['plant_species_id' => $id]);
        if ($species === null) {
            throw new NotFoundHttpException('Espécie não encontrada.');
        }

        try {
            $response = Yii::$app->visitTargetApi->toggle('species', $id);
            Yii::$app->session->setFlash('success', $response['message'] ?? 'Lista Quero visitar atualizada.');
        } catch (\Throwable $exception) {
            Yii::warning('Visit target backend toggle species failed: ' . $exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Não foi possível atualizar Quero visitar no backend comum. Confirma que o backend está atualizado e tenta novamente.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['visit/index']);
    }
    public function actionTogglePublication(int $id)
    {
        $publication = Publication::findOne(['publication_id' => $id]);
        if ($publication === null) {
            throw new NotFoundHttpException('Publicação não encontrada.');
        }

        try {
            $response = Yii::$app->visitTargetApi->toggle('publication', $id);
            Yii::$app->session->setFlash('success', $response['message'] ?? 'Lista Quero visitar atualizada.');
        } catch (\Throwable $exception) {
            Yii::warning('Visit target backend toggle publication failed: ' . $exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Não foi possível atualizar Quero visitar no backend comum. Confirma que o backend está atualizado e tenta novamente.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['visit/index']);
    }
    public function actionToggleObservation(int $id): array|Response
    {
        $observation = Observation::findOne(['observation_id' => $id]);
        if ($observation === null || !$observation->hasCoordinates()) {
            throw new NotFoundHttpException('Observação não encontrada.');
        }

        try {
            $response = Yii::$app->visitTargetApi->toggle('observation', $id);
            $saved = (bool) ($response['saved'] ?? false);
            $message = $response['message'] ?? 'Lista Quero visitar atualizada.';
        } catch (\Throwable $exception) {
            Yii::warning('Visit target backend toggle observation failed: ' . $exception->getMessage(), __METHOD__);
            $saved = false;
            $message = 'Não foi possível atualizar Quero visitar no backend comum. Confirma que o backend está atualizado e tenta novamente.';
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'success' => false,
                    'saved' => false,
                    'message' => $message,
                ];
            }
            Yii::$app->session->setFlash('error', $message);
            return $this->redirect(Yii::$app->request->referrer ?: ['visit/index']);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'success' => true,
                'saved' => $saved,
                'message' => $message,
            ];
        }

        Yii::$app->session->setFlash('success', $message);
        return $this->redirect(Yii::$app->request->referrer ?: ['visit/index']);
    }
    public function actionCreateRoute(): Response
    {
        $targets = SavedVisitTarget::find()
            ->with(['observation', 'publication.observation', 'plantSpecies'])
            ->where(['user_id' => Yii::$app->user->id])
            ->andWhere(['or', ['notes' => null], ['<>', 'notes', self::CONSUMED_VISIT_TARGET_NOTE]])
            ->orderBy(['created_at' => SORT_ASC, 'saved_visit_target_id' => SORT_ASC])
            ->all();

        if (empty($targets)) {
            Yii::$app->session->setFlash('error', 'Primeiro tens de marcar no mapa os pontos por onde queres passar.');
            return $this->redirect(['visit/index']);
        }

        $routePlan = new RoutePlan();
        $routePlan->user_id = (int) Yii::$app->user->id;
        if (!$routePlan->load(Yii::$app->request->post())) {
            Yii::$app->session->setFlash('error', 'Não foi possível ler os dados do percurso.');
            return $this->redirect(['visit/index']);
        }

        if (!$routePlan->validate(['name', 'description'])) {
            $firstError = $routePlan->getFirstError('name') ?: $routePlan->getFirstError('description') ?: 'Reve os dados do percurso.';
            Yii::$app->session->setFlash('error', $firstError);
            return $this->redirect(['visit/index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$routePlan->save(false)) {
                throw new \RuntimeException('Não foi possível criar o percurso.');
            }

            $visitOrder = 1;
            $usedTargetIds = [];
            foreach ($targets as $target) {
                $observation = $target->getMapObservation();
                if ($observation === null || !$observation->hasCoordinates()) {
                    continue;
                }

                $point = new RoutePlanPoint([
                    'route_plan_id' => $routePlan->route_plan_id,
                    'saved_visit_target_id' => $target->saved_visit_target_id,
                    'visit_order' => $visitOrder,
                    'notes' => 'Gerado a partir de Quero visitar.',
                ]);

                if (!$point->save()) {
                    throw new \RuntimeException('Não foi possível guardar uma das paragens do percurso.');
                }

                $usedTargetIds[] = (int) $target->saved_visit_target_id;

                $visitOrder++;
            }

            if ($visitOrder === 1) {
                throw new \RuntimeException('Os alvos selecionados ainda não tem coordenadas válidas para gerar um percurso.');
            }

            SavedVisitTarget::updateAll(['notes' => self::CONSUMED_VISIT_TARGET_NOTE], ['saved_visit_target_id' => $usedTargetIds, 'user_id' => Yii::$app->user->id]);

            $transaction->commit();
            Yii::$app->session->setFlash('success', 'Percurso criado com os pontos que escolheste no mapa.');
            return $this->redirect(['route-plan/view', 'id' => $routePlan->route_plan_id]);
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', $exception->getMessage());
            return $this->redirect(['visit/index']);
        }
    }

    public function actionRemove(int $id)
    {
        $target = SavedVisitTarget::findOne(['saved_visit_target_id' => $id]);
        if ($target === null) {
            throw new NotFoundHttpException('Alvo de visita não encontrado.');
        }

        if ((int) $target->user_id !== (int) Yii::$app->user->id) {
            throw new ForbiddenHttpException('Não podes remover alvos de visita de outro utilizador.');
        }

        try {
            Yii::$app->visitTargetApi->remove($id);
            Yii::$app->session->setFlash('success', 'Alvo removido da tua lista de visita.');
        } catch (\Throwable $exception) {
            Yii::warning('Visit target backend remove failed: ' . $exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Não foi possível remover este alvo no backend comum. Confirma que o backend está atualizado e tenta novamente.');
        }

        return $this->redirect(['visit/index']);
    }
}



