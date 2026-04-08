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
                'title' => $observation->getResolvedCommonName() ?: 'Observacao botanica',
                'scientificName' => $observation->getResolvedScientificName() ?: 'Sem classificacao enriquecida',
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
            throw new NotFoundHttpException('Especie nao encontrada.');
        }

        $existing = SavedVisitTarget::findOne([
            'user_id' => Yii::$app->user->id,
            'plant_species_id' => $species->plant_species_id,
        ]);

        if ($existing !== null) {
            $existing->delete();
            Yii::$app->session->setFlash('success', 'Especie removida da tua lista de visita.');
        } else {
            $target = new SavedVisitTarget([
                'user_id' => Yii::$app->user->id,
                'plant_species_id' => $species->plant_species_id,
            ]);
            $target->save();
            Yii::$app->session->setFlash('success', 'Especie adicionada a Quero visitar.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['visit/index']);
    }

    public function actionTogglePublication(int $id)
    {
        $publication = Publication::findOne(['publication_id' => $id]);
        if ($publication === null) {
            throw new NotFoundHttpException('Publicacao nao encontrada.');
        }

        $existing = SavedVisitTarget::findOne([
            'user_id' => Yii::$app->user->id,
            'publication_id' => $publication->publication_id,
        ]);

        if ($existing !== null) {
            $existing->delete();
            Yii::$app->session->setFlash('success', 'Publicacao removida da tua lista de visita.');
        } else {
            $target = new SavedVisitTarget([
                'user_id' => Yii::$app->user->id,
                'publication_id' => $publication->publication_id,
            ]);
            $target->save();
            Yii::$app->session->setFlash('success', 'Publicacao adicionada a Quero visitar.');
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['visit/index']);
    }

    public function actionToggleObservation(int $id): array|Response
    {
        $observation = Observation::findOne(['observation_id' => $id]);
        if ($observation === null || !$observation->hasCoordinates()) {
            throw new NotFoundHttpException('Observacao nao encontrada.');
        }

        $existing = SavedVisitTarget::findOne([
            'user_id' => Yii::$app->user->id,
            'observation_id' => $observation->observation_id,
        ]);

        $saved = false;
        $message = '';
        if ($existing !== null) {
            $existing->delete();
            $message = 'Observacao removida da tua lista de visita.';
        } else {
            $target = new SavedVisitTarget([
                'user_id' => Yii::$app->user->id,
                'observation_id' => $observation->observation_id,
            ]);
            $target->save();
            $saved = true;
            $message = 'Observacao adicionada a Quero visitar.';
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
            ->orderBy(['created_at' => SORT_ASC, 'saved_visit_target_id' => SORT_ASC])
            ->all();

        if (empty($targets)) {
            Yii::$app->session->setFlash('error', 'Primeiro tens de marcar no mapa os pontos por onde queres passar.');
            return $this->redirect(['visit/index']);
        }

        $routePlan = new RoutePlan();
        $routePlan->user_id = (int) Yii::$app->user->id;
        if (!$routePlan->load(Yii::$app->request->post())) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel ler os dados do percurso.');
            return $this->redirect(['visit/index']);
        }

        if (!$routePlan->validate(['name', 'description'])) {
            $firstError = $routePlan->getFirstError('name') ?: $routePlan->getFirstError('description') ?: 'Revê os dados do percurso.';
            Yii::$app->session->setFlash('error', $firstError);
            return $this->redirect(['visit/index']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$routePlan->save(false)) {
                throw new \RuntimeException('Nao foi possivel criar o percurso.');
            }

            $visitOrder = 1;
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
                    throw new \RuntimeException('Nao foi possivel guardar uma das paragens do percurso.');
                }

                $visitOrder++;
            }

            if ($visitOrder === 1) {
                throw new \RuntimeException('Os alvos selecionados ainda nao tem coordenadas validas para gerar um percurso.');
            }

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
            throw new NotFoundHttpException('Alvo de visita nao encontrado.');
        }

        if ((int) $target->user_id !== (int) Yii::$app->user->id) {
            throw new ForbiddenHttpException('Nao podes remover alvos de visita de outro utilizador.');
        }

        $target->delete();
        Yii::$app->session->setFlash('success', 'Alvo removido da tua lista de visita.');

        return $this->redirect(['visit/index']);
    }
}



