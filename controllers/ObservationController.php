<?php

namespace app\controllers;

use app\models\Observation;
use RuntimeException;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ObservationController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete'],
                        'roles' => ['@'],
                        'matchCallback' => static function () {
                            $identity = Yii::$app->user->identity;
                            if ($identity === null) {
                                return false;
                            }
                            return in_array(Yii::$app->requestedAction?->id, ['update', 'delete'], true) || $identity->isAdmin();
                        },
                    ],
                ],
                'denyCallback' => static function () {
                    if (Yii::$app->user->isGuest) {
                        return Yii::$app->user->loginRequired();
                    }
                    throw new ForbiddenHttpException('Nao tens permissao para criar observacoes manualmente.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $status = trim((string) Yii::$app->request->get('status', 'all'));
        $queryText = trim((string) Yii::$app->request->get('q', ''));
        $myObservationsOnly = (bool) Yii::$app->request->get('my', false);
        $allowedStatuses = ['all', Observation::SYNC_PENDING, Observation::SYNC_SYNCED, Observation::SYNC_FAILED, 'PUBLISHED', Observation::STATUS_MANUAL_REVIEW];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $pagination = new Pagination(['totalCount' => 0, 'pageSize' => 5]);
        try {
            $result = Yii::$app->observationApi->listObservations($queryText, $status, $myObservationsOnly, $pagination->getPage(), $pagination->getPageSize());
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Nao foi possivel carregar as observacoes a partir da API.');
            $result = ['items' => [], 'totalCount' => 0, 'summary' => ['total' => 0, 'published' => 0, 'pending' => 0, 'failed' => 0]];
        }
        $pagination->totalCount = (int) $result['totalCount'];

        return $this->render('index', [
            'observations' => $result['items'],
            'pagination' => $pagination,
            'queryText' => $queryText,
            'status' => $status,
            'summary' => $result['summary'],
        ]);
    }

    public function actionView(int $id): string
    {
        try {
            $observation = Yii::$app->observationApi->getObservationById($id);
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $observation = null;
        }

        if ($observation === null) {
            throw new NotFoundHttpException('Observacao nao encontrada.');
        }

        return $this->render('view', ['observation' => $observation]);
    }

    public function actionCreate()
    {
        $model = new Observation();
        $model->user_id = (int) Yii::$app->user->id;
        $model->observed_at = date('Y-m-d\TH:i');
        $model->captured_at = time();
        $model->confidence = 0;
        $model->sync_status = Observation::SYNC_PENDING;
        $model->is_synced = false;
        $model->is_published = false;

        $latitude = Yii::$app->request->get('latitude');
        $longitude = Yii::$app->request->get('longitude');
        if (is_numeric($latitude) && is_numeric($longitude)) {
            $model->latitude = (float) $latitude;
            $model->longitude = (float) $longitude;
        }

        if ($model->load(Yii::$app->request->post())) {
            try {
                $response = Yii::$app->observationApi->saveObservation($this->observationPayload($model));
                Yii::$app->session->setFlash('success', 'Observacao criada com sucesso.');
                return $this->redirect(['observation/view', 'id' => (int) ($response['observationId'] ?? 0)]);
            } catch (RuntimeException $exception) {
                $model->addError('notes', 'Nao foi possivel guardar a observacao no backend: ' . $exception->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $model,
            'userOptions' => $this->getUserOptions(),
            'speciesOptions' => $this->getSpeciesOptions(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $model = $this->findApiModel($id);
        $this->ensureManageAccess($model);

        if (!empty($model->observed_at)) {
            $model->observed_at = date('Y-m-d\TH:i', strtotime((string) $model->observed_at));
        }

        if ($model->load(Yii::$app->request->post())) {
            try {
                if ($model->needsManualReview() && (Yii::$app->user->identity?->isAdmin() ?? false)) {
                    Yii::$app->observationApi->reviewObservation(
                        (string) $model->device_observation_id,
                        $this->manualReviewPayload($model)
                    );
                    Yii::$app->session->setFlash('success', 'Observacao identificada manualmente com sucesso.');
                } else {
                    Yii::$app->observationApi->saveObservation($this->observationPayload($model));
                    Yii::$app->session->setFlash('success', 'Observacao atualizada com sucesso.');
                }
                return $this->redirect(['observation/view', 'id' => $model->observation_id]);
            } catch (RuntimeException $exception) {
                $model->addError('notes', 'Nao foi possivel atualizar a observacao no backend: ' . $exception->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $model,
            'userOptions' => $this->getUserOptions(),
            'speciesOptions' => $this->getSpeciesOptions(),
        ]);
    }

    public function actionDelete(int $id)
    {
        if (!(Yii::$app->user->identity?->isAdmin() ?? false)) {
            throw new ForbiddenHttpException('Nao tens permissao para remover esta observacao.');
        }

        try {
            Yii::$app->observationApi->deleteObservation($id);
            Yii::$app->session->setFlash('success', 'Observacao removida com sucesso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel remover a observacao no backend: ' . $exception->getMessage());
        }

        return $this->redirect(['observation/index']);
    }

    private function getUserOptions(): array
    {
        try {
            $users = Yii::$app->accountApi->listUsers();
        } catch (RuntimeException) {
            $users = [[
                'userId' => Yii::$app->user->id,
                'displayName' => Yii::$app->user->identity?->getFullName(),
                'username' => Yii::$app->user->identity?->username,
            ]];
        }

        $options = [];
        foreach ($users as $user) {
            if (!is_array($user)) {
                continue;
            }
            $userId = (int) ($user['userId'] ?? $user['user_id'] ?? 0);
            $name = trim((string) ($user['displayName'] ?? (($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''))));
            $username = (string) ($user['username'] ?? 'sem-username');
            if ($userId > 0) {
                $options[$userId] = ($name !== '' ? $name : $username) . ' (@' . ($username ?: 'sem-username') . ')';
            }
        }
        return $options;
    }

    private function getSpeciesOptions(): array
    {
        try {
            $species = Yii::$app->speciesApi->listSpecies('', 'species', 0, 1000)['items'];
        } catch (RuntimeException) {
            $species = [];
        }

        $options = [];
        foreach ($species as $item) {
            $options[$item->plant_species_id] = $item->getDisplayName() . ' (' . $item->scientific_name . ')';
        }
        return $options;
    }

    private function ensureManageAccess(Observation $observation): void
    {
        $identity = Yii::$app->user->identity;
        if ($identity === null) {
            throw new ForbiddenHttpException('Precisas de iniciar sessao para editar observacoes.');
        }
        if (!$identity->isAdmin() && (int) $identity->user_id !== (int) $observation->user_id) {
            throw new ForbiddenHttpException('Nao tens permissao para editar esta observacao.');
        }
    }

    private function findApiModel(int $id): Observation
    {
        $apiObservation = Yii::$app->observationApi->getObservationById($id);
        if ($apiObservation === null) {
            throw new NotFoundHttpException('Observacao nao encontrada.');
        }

        $model = new Observation();
        $model->observation_id = $apiObservation->observation_id;
        $model->device_observation_id = $apiObservation->device_observation_id;
        $model->user_id = $apiObservation->user_id ?? (int) Yii::$app->user->id;
        $model->plant_species_id = $apiObservation->plant_species_id;
        $model->latitude = $apiObservation->latitude;
        $model->longitude = $apiObservation->longitude;
        $model->observed_at = $apiObservation->observed_at;
        $model->notes = $apiObservation->notes;
        $model->confidence = $apiObservation->confidence;
        $model->sync_status = $apiObservation->sync_status;
        $model->is_published = $apiObservation->is_published;
        $model->predicted_scientific_name = $apiObservation->predicted_scientific_name;
        $model->requires_manual_identification = $apiObservation->requires_manual_identification;
        $model->setIsNewRecord(false);
        return $model;
    }

    private function observationPayload(Observation $model): array
    {
        $observedAt = trim((string) $model->observed_at);
        $timestamp = $observedAt !== '' ? strtotime(str_replace('T', ' ', $observedAt)) : time();
        $species = $this->speciesData((int) $model->plant_species_id);

        return [
            'deviceObservationId' => trim((string) $model->device_observation_id) !== '' ? $model->device_observation_id : null,
            'userId' => (int) $model->user_id,
            'plantSpeciesId' => $model->plant_species_id ? (int) $model->plant_species_id : null,
            'capturedAt' => time(),
            'predictedScientificName' => $species['scientificName'] ?? 'Observacao botanica',
            'enrichedScientificName' => $species['scientificName'] ?? null,
            'enrichedCommonName' => $species['commonName'] ?? null,
            'enrichedFamily' => $species['family'] ?? null,
            'confidence' => 0,
            'latitude' => $model->latitude !== null ? (float) $model->latitude : null,
            'longitude' => $model->longitude !== null ? (float) $model->longitude : null,
            'observedAt' => date('c', $timestamp ?: time()),
            'isPublished' => (bool) $model->is_published,
            'syncStatus' => $model->sync_status ?: Observation::SYNC_PENDING,
            'requiresManualIdentification' => $model->needsManualReview(),
            'notes' => trim((string) $model->notes) !== '' ? $model->notes : null,
        ];
    }

    private function manualReviewPayload(Observation $model): array
    {
        $species = $this->speciesData((int) $model->plant_species_id);
        if ($species === []) {
            throw new RuntimeException('Seleciona uma especie valida para concluir a identificacao manual.');
        }

        return [
            'scientificName' => $species['scientificName'] ?? null,
            'commonName' => $species['commonName'] ?? null,
            'family' => $species['family'] ?? null,
            'notes' => trim((string) $model->notes) !== '' ? $model->notes : null,
        ];
    }

    private function speciesData(int $speciesId): array
    {
        if ($speciesId <= 0) {
            return [];
        }
        try {
            $species = Yii::$app->speciesApi->listSpecies('', 'species', 0, 1000)['items'];
        } catch (RuntimeException) {
            return [];
        }
        foreach ($species as $item) {
            if ((int) $item->plant_species_id === $speciesId) {
                return [
                    'scientificName' => $item->scientific_name,
                    'commonName' => $item->common_name,
                    'family' => $item->family,
                ];
            }
        }
        return [];
    }
}
