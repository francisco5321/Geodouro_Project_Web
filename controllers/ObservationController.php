<?php

namespace app\controllers;

use app\models\AppUser;
use app\models\Observation;
use app\models\PlantSpecies;
use Yii;
use yii\data\Pagination;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

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

                            if (in_array(Yii::$app->requestedAction?->id, ['update', 'delete'], true)) {
                                return true;
                            }

                            return $identity->isAdmin();
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
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $status = trim((string) Yii::$app->request->get('status', 'all'));
        $queryText = trim((string) Yii::$app->request->get('q', ''));
        $myObservationsOnly = (bool) Yii::$app->request->get('my', false);
        $allowedStatuses = ['all', Observation::SYNC_PENDING, Observation::SYNC_SYNCED, Observation::SYNC_FAILED, 'PUBLISHED'];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $query = Observation::find()
            ->with(['user', 'plantSpecies', 'publication.user'])
            ->joinWith([
                'plantSpecies' => static function ($query) {
                    $query->alias('species');
                },
            ])
            ->orderBy(['observed_at' => SORT_DESC, 'observation_id' => SORT_DESC]);

        if ($queryText !== '') {
            $query->andWhere([
                'or',
                ['ilike', 'species.common_name', $queryText],
                ['ilike', 'species.scientific_name', $queryText],
                ['ilike', 'enriched_common_name', $queryText],
                ['ilike', 'enriched_scientific_name', $queryText],
                ['ilike', 'predicted_scientific_name', $queryText],
            ]);
        }

        // Filtrar por utilizador actual se my=1
        if ($myObservationsOnly) {
            $query->andWhere(['user_id' => Yii::$app->user->id]);
        }

        if ($status === 'PUBLISHED') {
            $query->andWhere(['is_published' => true]);
        } elseif ($status !== 'all') {
            $query->andWhere(['sync_status' => $status]);
        }

        $pagination = new Pagination([
            'totalCount' => (clone $query)->count(),
            'pageSize' => 5,
        ]);

        $observations = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $summaryQuery = Observation::find()->select([
            'total' => new Expression('COUNT(*)'),
            'published' => new Expression('SUM(CASE WHEN is_published THEN 1 ELSE 0 END)'),
            'pending' => new Expression('SUM(CASE WHEN sync_status = :pending THEN 1 ELSE 0 END)', [
                ':pending' => Observation::SYNC_PENDING,
            ]),
            'failed' => new Expression('SUM(CASE WHEN sync_status = :failed THEN 1 ELSE 0 END)', [
                ':failed' => Observation::SYNC_FAILED,
            ]),
        ])->asArray();

        if ($myObservationsOnly) {
            $summaryQuery->andWhere(['user_id' => Yii::$app->user->id]);
        }

        $summaryRow = $summaryQuery->one() ?: [];
        $summary = [
            'total' => (int) ($summaryRow['total'] ?? 0),
            'published' => (int) ($summaryRow['published'] ?? 0),
            'pending' => (int) ($summaryRow['pending'] ?? 0),
            'failed' => (int) ($summaryRow['failed'] ?? 0),
        ];

        return $this->render('index', [
            'observations' => $observations,
            'pagination' => $pagination,
            'queryText' => $queryText,
            'status' => $status,
            'summary' => $summary,
        ]);
    }

    public function actionView(int $id): string
    {
        $observation = Observation::find()
            ->with(['user', 'plantSpecies', 'observationImages', 'publication.user'])
            ->where(['observation_id' => $id])
            ->one();

        if ($observation === null) {
            throw new NotFoundHttpException('Observação não encontrada.');
        }

        return $this->render('view', [
            'observation' => $observation,
        ]);
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
            if (!empty($model->observed_at)) {
                $observedAt = str_replace('T', ' ', (string) $model->observed_at);
                $model->observed_at = strlen($observedAt) === 16 ? $observedAt . ':00' : $observedAt;
            }
            if (empty($model->captured_at)) {
                $model->captured_at = time();
            }
            $model->confidence = 0;
            $model->sync_status = Observation::SYNC_PENDING;
            $model->is_synced = false;
            $model->is_published = false;
            $model->enriched_wikipedia_url = null;
            $model->enriched_photo_url = null;
            $this->fillSpeciesClassification($model);
            if (!$this->saveUploadedObservationImage($model)) {
                return $this->render('create', [
                    'model' => $model,
                    'userOptions' => $this->getUserOptions(),
                    'speciesOptions' => $this->getSpeciesOptions(),
                ]);
            }

            foreach ([
                'device_observation_id',
                'image_uri',
                'predicted_scientific_name',
                'enriched_scientific_name',
                'enriched_common_name',
                'enriched_family',
                'enriched_wikipedia_url',
                'enriched_photo_url',
                'notes',
            ] as $attribute) {
                if (trim((string) $model->$attribute) === '') {
                    $model->$attribute = null;
                }
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Observação criada com sucesso.');
                return $this->redirect(['observation/view', 'id' => $model->observation_id]);
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
        $model = $this->findModel($id);
        $this->ensureManageAccess($model);

        if (!empty($model->observed_at)) {
            $model->observed_at = date('Y-m-d\TH:i', strtotime((string) $model->observed_at));
        }

        if ($model->load(Yii::$app->request->post())) {
            if (!empty($model->observed_at)) {
                $observedAt = str_replace('T', ' ', (string) $model->observed_at);
                $model->observed_at = strlen($observedAt) === 16 ? $observedAt . ':00' : $observedAt;
            }
            if (empty($model->captured_at)) {
                $model->captured_at = null;
            }
            $model->confidence = 0;
            $model->sync_status = Observation::SYNC_PENDING;
            $model->enriched_wikipedia_url = null;
            $model->enriched_photo_url = null;
            $this->fillSpeciesClassification($model);
            if (!$this->saveUploadedObservationImage($model)) {
                return $this->render('update', [
                    'model' => $model,
                    'userOptions' => $this->getUserOptions(),
                    'speciesOptions' => $this->getSpeciesOptions(),
                ]);
            }

            foreach ([
                'device_observation_id',
                'image_uri',
                'predicted_scientific_name',
                'enriched_scientific_name',
                'enriched_common_name',
                'enriched_family',
                'enriched_wikipedia_url',
                'enriched_photo_url',
                'notes',
            ] as $attribute) {
                if (trim((string) $model->$attribute) === '') {
                    $model->$attribute = null;
                }
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Observação atualizada com sucesso.');
                return $this->redirect(['observation/view', 'id' => $model->observation_id]);
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
        $observation = $this->findModel($id);

        if (!(Yii::$app->user->identity?->isAdmin() ?? false)) {
            throw new ForbiddenHttpException('Não tens permissão para remover esta observação.');
        }

        $observation->delete();
        Yii::$app->session->setFlash('success', 'Observação removida com sucesso.');

        return $this->redirect(['observation/index']);
    }

    private function getUserOptions(): array
    {
        $users = AppUser::find()
            ->where(['is_authenticated' => true])
            ->orderBy(['first_name' => SORT_ASC, 'last_name' => SORT_ASC, 'username' => SORT_ASC])
            ->all();

        $options = [];
        foreach ($users as $user) {
            $options[$user->user_id] = $user->getFullName() . ' (@' . ($user->username ?: 'sem-username') . ')';
        }

        return $options;
    }

    private function getSpeciesOptions(): array
    {
        $species = PlantSpecies::find()
            ->orderBy(['common_name' => SORT_ASC, 'scientific_name' => SORT_ASC])
            ->all();

        $options = [];
        foreach ($species as $item) {
            $options[$item->plant_species_id] = $item->getDisplayName() . ' (' . $item->scientific_name . ')';
        }

        return $options;
    }

    private function saveUploadedObservationImage(Observation $model): bool
    {
        $uploadedFile = UploadedFile::getInstanceByName('observation_image_file');
        if ($uploadedFile === null || $uploadedFile->error === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        $extension = strtolower($uploadedFile->extension ?: '');
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions, true) || !str_starts_with((string) $uploadedFile->type, 'image/') || @getimagesize($uploadedFile->tempName) === false) {
            $model->addError('image_uri', 'Seleciona um ficheiro de imagem valido.');
            return false;
        }

        if ($uploadedFile->hasError) {
            $model->addError('image_uri', 'Nao foi possivel carregar a imagem selecionada.');
            return false;
        }

        $basePath = Yii::$app->params['backendUploadsPath'] ?? null;
        if (!$basePath) {
            $model->addError('image_uri', 'Diretorio de uploads nao configurado.');
            return false;
        }

        $relativeDirectory = 'observations/manual';
        $targetDirectory = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDirectory);
        FileHelper::createDirectory($targetDirectory);

        $fileName = 'observation_' . date('Ymd_His') . '_' . Yii::$app->security->generateRandomString(8) . '.' . $extension;
        $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $fileName;

        if (!$uploadedFile->saveAs($targetPath)) {
            $model->addError('image_uri', 'Nao foi possivel guardar a imagem selecionada.');
            return false;
        }

        $model->image_uri = $relativeDirectory . '/' . $fileName;
        return true;
    }

    private function fillSpeciesClassification(Observation $model): void
    {
        if (empty($model->plant_species_id)) {
            $model->predicted_scientific_name = null;
            $model->enriched_scientific_name = null;
            $model->enriched_common_name = null;
            $model->enriched_family = null;
            return;
        }

        $species = PlantSpecies::findOne(['plant_species_id' => $model->plant_species_id]);
        if ($species === null) {
            return;
        }

        $model->predicted_scientific_name = $species->scientific_name;
        $model->enriched_scientific_name = $species->scientific_name;
        $model->enriched_common_name = $species->common_name;
        $model->enriched_family = $species->family;
    }

    private function ensureManageAccess(Observation $observation): void
    {
        $identity = Yii::$app->user->identity;
        if ($identity === null) {
            throw new ForbiddenHttpException('Precisas de iniciar sessão para editar observações.');
        }

        if (!$identity->isAdmin() && (int) $identity->user_id !== (int) $observation->user_id) {
            throw new ForbiddenHttpException('Não tens permissão para editar esta observação.');
        }
    }

    private function findModel(int $id): Observation
    {
        $model = Observation::findOne(['observation_id' => $id]);
        if ($model === null) {
            throw new NotFoundHttpException('Observação não encontrada.');
        }

        return $model;
    }
}
