<?php

namespace app\controllers;

use app\models\Observation;
use app\models\ObservationForm;
use RuntimeException;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ObservationController extends Controller
{
    /**
     * @var array<int, array{scientificName: ?string, commonName: ?string, family: ?string}>
     */
    private array $speciesDataOverrides = [];

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create', 'update', 'delete', 'request-review'],
                        'roles' => ['@'],
                        'matchCallback' => static function () {
                            $identity = Yii::$app->user->identity;
                            if ($identity === null) {
                                return false;
                            }

                            return in_array(Yii::$app->requestedAction?->id, ['update', 'delete', 'request-review'], true) || $identity->isAdmin();
                        },
                    ],
                ],
                'denyCallback' => static function () {
                    if (Yii::$app->user->isGuest) {
                        return Yii::$app->user->loginRequired();
                    }

                    throw new ForbiddenHttpException('Não tens permissão para criar observações manualmente.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'request-review' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $status = trim((string) Yii::$app->request->get('status', 'all'));
        $queryText = trim((string) Yii::$app->request->get('q', ''));
        $isAdmin = Yii::$app->user->identity?->isAdmin() ?? false;
        $myParam = Yii::$app->request->get('my');
        $myObservationsOnly = $myParam === null
            ? !$isAdmin
            : filter_var($myParam, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $allowedStatuses = ['all', Observation::SYNC_PENDING, Observation::SYNC_SYNCED, Observation::SYNC_FAILED, 'PUBLISHED', Observation::STATUS_MANUAL_REVIEW];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }
        if (!$isAdmin && $status === Observation::STATUS_MANUAL_REVIEW) {
            $status = 'all';
        }

        $pagination = new Pagination(['totalCount' => 0, 'pageSize' => 5, 'validatePage' => false]);
        try {
            $result = Yii::$app->observationApi->listObservations($queryText, $status, $myObservationsOnly, $pagination->getPage(), $pagination->getPageSize());
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Não foi possível carregar as observações a partir da API.');
            $result = ['items' => [], 'totalCount' => 0, 'summary' => ['total' => 0, 'published' => 0, 'pending' => 0, 'failed' => 0]];
        }
        $pagination->totalCount = (int) $result['totalCount'];

        return $this->render('index', [
            'observations' => $result['items'],
            'pagination' => $pagination,
            'queryText' => $queryText,
            'status' => $status,
            'myObservationsOnly' => $myObservationsOnly,
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
            throw new NotFoundHttpException('Observação não encontrada.');
        }

        return $this->render('view', [
            'observation' => $observation,
            'returnUrl' => $this->resolveObservationReturnUrl(),
        ]);
    }

    public function actionCreate()
    {
        $model = new ObservationForm();
        $model->user_id = (int) Yii::$app->user->id;
        $model->observed_at = date('Y-m-d\TH:i');
        $model->captured_at = time();
        $model->confidence = 0;
        $model->sync_status = Observation::SYNC_PENDING;
        $model->is_synced = false;
        $model->is_published = false;
        $model->isNewRecord = true;

        $latitude = Yii::$app->request->get('latitude');
        $longitude = Yii::$app->request->get('longitude');
        if (is_numeric($latitude) && is_numeric($longitude)) {
            $model->latitude = (float) $latitude;
            $model->longitude = (float) $longitude;
        }

        if ($model->load(Yii::$app->request->post())) {
            try {
                $response = Yii::$app->observationApi->saveObservation($this->observationPayload($model));
                Yii::$app->session->setFlash('success', 'Observação criada com sucesso.');

                return $this->redirect(['observation/view', 'id' => (int) ($response['observationId'] ?? 0)]);
            } catch (RuntimeException $exception) {
                $model->addError('notes', 'Não foi possível guardar a observação no backend: ' . $exception->getMessage());
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
                    if (!$this->prepareSpeciesForManualReview($model)) {
                        return $this->render('update', [
                            'model' => $model,
                            'userOptions' => $this->getUserOptions(),
                            'speciesOptions' => $this->getSpeciesOptions(),
                        ]);
                    }

                    Yii::$app->observationApi->reviewObservation(
                        (string) $model->device_observation_id,
                        $this->manualReviewPayload($model)
                    );
                    Yii::$app->session->setFlash('success', 'Observação identificada manualmente com sucesso.');
                } else {
                    Yii::$app->observationApi->saveObservation($this->observationPayload($model));
                    Yii::$app->session->setFlash('success', 'Observação atualizada com sucesso.');
                }

                return $this->redirect(['observation/view', 'id' => $model->observation_id]);
            } catch (RuntimeException $exception) {
                $model->addError('notes', 'Não foi possível atualizar a observação no backend: ' . $exception->getMessage());
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
            throw new ForbiddenHttpException('Não tens permissão para remover esta observação.');
        }

        try {
            Yii::$app->observationApi->deleteObservation($id);
            Yii::$app->session->setFlash('success', 'Observação removida com sucesso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Não foi possível remover a observação no backend: ' . $exception->getMessage());
        }

        return $this->redirect(['observation/index']);
    }

    public function actionRequestReview(int $id)
    {
        $model = $this->findApiModel($id);
        $this->ensureManageAccess($model);

        if ($model->needsManualReview()) {
            Yii::$app->session->setFlash('info', 'Esta observação já está na fila de revisão manual.');
            return $this->redirect(['observation/view', 'id' => $model->observation_id]);
        }

        if ($model->is_published) {
            Yii::$app->session->setFlash('error', 'Não é possível reenviar uma observação já publicada para revisão manual.');
            return $this->redirect(['observation/view', 'id' => $model->observation_id]);
        }

        try {
            Yii::$app->observationApi->saveObservation($this->manualReviewRequestPayload($model));
            Yii::$app->session->setFlash('success', 'Observação enviada para a administração rever manualmente.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Não foi possível enviar a observação para revisão manual: ' . $exception->getMessage());
        }

        return $this->redirect(['observation/view', 'id' => $model->observation_id]);
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

    private function ensureManageAccess(ObservationForm $observation): void
    {
        $identity = Yii::$app->user->identity;
        if ($identity === null) {
            throw new ForbiddenHttpException('Precisas de iniciar sessão para editar observações.');
        }
        if (!$identity->isAdmin() && (int) $identity->user_id !== (int) $observation->user_id) {
            throw new ForbiddenHttpException('Não tens permissão para editar esta observação.');
        }
    }

    private function findApiModel(int $id): ObservationForm
    {
        $apiObservation = Yii::$app->observationApi->getObservationById($id);
        if ($apiObservation === null) {
            throw new NotFoundHttpException('Observação não encontrada.');
        }

        $model = new ObservationForm();
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
        $model->is_synced = $apiObservation->is_synced;
        $model->captured_at = $apiObservation->captured_at;
        $model->predicted_scientific_name = $apiObservation->predicted_scientific_name;
        $model->enriched_scientific_name = $apiObservation->enriched_scientific_name;
        $model->enriched_common_name = $apiObservation->enriched_common_name;
        $model->enriched_family = $apiObservation->enriched_family;
        $model->requires_manual_identification = $apiObservation->requires_manual_identification;
        $model->isNewRecord = false;

        return $model;
    }

    private function resolveObservationReturnUrl(): string
    {
        $fallbackUrl = Url::to(['observation/index']);
        $returnUrl = trim((string) Yii::$app->request->get('returnUrl', ''));
        if ($returnUrl === '') {
            return $fallbackUrl;
        }

        $parsedUrl = parse_url($returnUrl);
        if ($parsedUrl === false) {
            return $fallbackUrl;
        }

        if (isset($parsedUrl['scheme']) || isset($parsedUrl['host'])) {
            return $fallbackUrl;
        }

        return str_starts_with($returnUrl, '/') ? $returnUrl : $fallbackUrl;
    }

    private function observationPayload(ObservationForm $model): array
    {
        $observedAt = trim((string) $model->observed_at);
        $timestamp = $observedAt !== '' ? strtotime(str_replace('T', ' ', $observedAt)) : time();
        $species = $this->speciesData((int) $model->plant_species_id);

        return [
            'deviceObservationId' => trim((string) $model->device_observation_id) !== '' ? $model->device_observation_id : null,
            'userId' => (int) $model->user_id,
            'plantSpeciesId' => $model->plant_species_id && (int) $model->plant_species_id > 0 ? (int) $model->plant_species_id : null,
            'capturedAt' => time(),
            'predictedScientificName' => $species['scientificName'] ?? 'Observação botanica',
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

    private function manualReviewPayload(ObservationForm $model): array
    {
        $species = $this->speciesData((int) $model->plant_species_id);
        if ($species === []) {
            throw new RuntimeException('Seleciona uma espécie válida para concluir a identificação manual.');
        }

        return [
            'plantSpeciesId' => $model->plant_species_id && (int) $model->plant_species_id > 0 ? (int) $model->plant_species_id : null,
            'scientificName' => $species['scientificName'] ?? null,
            'commonName' => $species['commonName'] ?? null,
            'family' => $species['family'] ?? null,
            'notes' => trim((string) $model->notes) !== '' ? $model->notes : null,
        ];
    }

    private function manualReviewRequestPayload(ObservationForm $model): array
    {
        $observedAt = trim((string) $model->observed_at);
        $timestamp = $observedAt !== '' ? strtotime(str_replace('T', ' ', $observedAt)) : time();
        $predictedScientificName = trim((string) $model->predicted_scientific_name);
        if ($predictedScientificName === '') {
            $predictedScientificName = trim((string) $model->getResolvedScientificName());
        }

        return [
            'deviceObservationId' => trim((string) $model->device_observation_id) !== '' ? $model->device_observation_id : null,
            'userId' => (int) $model->user_id,
            'plantSpeciesId' => null,
            'capturedAt' => $model->captured_at ?: time(),
            'predictedScientificName' => $predictedScientificName !== '' ? $predictedScientificName : 'Observação botânica',
            'enrichedScientificName' => null,
            'enrichedCommonName' => null,
            'enrichedFamily' => null,
            'confidence' => $model->confidence !== null ? (float) $model->confidence : 0,
            'latitude' => $model->latitude !== null ? (float) $model->latitude : null,
            'longitude' => $model->longitude !== null ? (float) $model->longitude : null,
            'observedAt' => date('c', $timestamp ?: time()),
            'isPublished' => false,
            'syncStatus' => $model->sync_status ?: Observation::SYNC_PENDING,
            'requiresManualIdentification' => true,
            'notes' => trim((string) $model->notes) !== '' ? $model->notes : null,
        ];
    }

    private function speciesData(int $speciesId): array
    {
        if ($speciesId <= 0) {
            return [];
        }
        if (isset($this->speciesDataOverrides[$speciesId])) {
            return $this->speciesDataOverrides[$speciesId];
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

    private function prepareSpeciesForManualReview(ObservationForm $model): bool
    {
        if (!$model->isNewSpeciesRequested()) {
            if (!$model->plant_species_id) {
                $model->addError('plant_species_id', 'Seleciona uma espécie para concluir a revisão manual.');
                return false;
            }

            return true;
        }

        if (!$model->validate([
            'new_species_scientific_name',
            'new_species_common_name',
            'new_species_family',
            'new_species_genus',
            'new_species_species',
        ])) {
            return false;
        }

        try {
            $createdSpecies = Yii::$app->speciesApi->createSpecies([
                'scientificName' => trim((string) $model->new_species_scientific_name),
                'commonName' => trim((string) $model->new_species_common_name),
                'family' => trim((string) $model->new_species_family),
                'genus' => trim((string) $model->new_species_genus),
                'species' => trim((string) $model->new_species_species),
            ]);
        } catch (RuntimeException $exception) {
            $model->addError('new_species_scientific_name', 'Não foi possível criar a nova espécie: ' . $exception->getMessage());
            return false;
        }

        if ((int) $createdSpecies->plant_species_id <= 0) {
            $model->addError('new_species_scientific_name', 'A espécie foi criada sem identificador válido no backend.');
            return false;
        }

        $model->plant_species_id = (int) $createdSpecies->plant_species_id;
        $this->speciesDataOverrides[$model->plant_species_id] = [
            'scientificName' => $createdSpecies->scientific_name ?: trim((string) $model->new_species_scientific_name),
            'commonName' => $createdSpecies->common_name ?: trim((string) $model->new_species_common_name),
            'family' => $createdSpecies->family ?: trim((string) $model->new_species_family),
        ];

        return true;
    }
}
