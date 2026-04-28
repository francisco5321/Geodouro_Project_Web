<?php

namespace app\controllers;

use app\models\Publication;
use RuntimeException;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class PublicationController extends Controller
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
                        'actions' => ['create', 'update', 'publish', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'publish' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $scope = trim((string) Yii::$app->request->get('scope', 'all'));
        if (Yii::$app->user->isGuest && $scope === 'mine') {
            $scope = 'all';
        }

        $pagination = new Pagination(['totalCount' => 0, 'pageSize' => 8]);
        try {
            $result = Yii::$app->publicationApi->listPublications($scope, $pagination->getPage(), $pagination->getPageSize());
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Nao foi possivel carregar as publicacoes a partir da API.');
            $result = ['items' => [], 'totalCount' => 0, 'summary' => ['total' => 0]];
        }
        $pagination->totalCount = (int) $result['totalCount'];

        return $this->render('index', [
            'publications' => $result['items'],
            'pagination' => $pagination,
            'summary' => $result['summary'],
            'scope' => $scope,
        ]);
    }

    public function actionView(int $id): string
    {
        try {
            $publication = Yii::$app->publicationApi->getPublicationById($id);
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $publication = null;
        }

        if ($publication === null) {
            throw new NotFoundHttpException('Publicacao nao encontrada.');
        }

        return $this->render('view', ['publication' => $publication]);
    }

    public function actionCreate(?int $observationId = null)
    {
        $publication = new Publication();
        $publication->status = Publication::STATUS_PUBLISHED;
        $publication->user_id = (int) Yii::$app->user->id;
        if ($observationId !== null) {
            $publication->observation_id = $observationId;
        }

        $observationOptions = $this->getEditableObservationOptions($publication->observation_id ?: null);
        if (empty($observationOptions)) {
            Yii::$app->session->setFlash('success', 'Nao ha observacoes elegiveis para criar uma nova publicacao.');
            return $this->redirect(['publication/index']);
        }

        if ($publication->load(Yii::$app->request->post())) {
            try {
                $observation = Yii::$app->observationApi->getObservationById((int) $publication->observation_id);
                if ($observation === null || $observation->device_observation_id === null) {
                    throw new RuntimeException('Observacao nao encontrada.');
                }
                $created = Yii::$app->publicationApi->publishObservation($observation->device_observation_id, $publication->title, $publication->description);
                Yii::$app->session->setFlash('success', 'Publicacao criada com sucesso.');
                return $this->redirect(['publication/view', 'id' => $created->publication_id]);
            } catch (RuntimeException $exception) {
                $publication->addError('observation_id', 'Nao foi possivel criar a publicacao no backend: ' . $exception->getMessage());
            }
        }

        return $this->render('create', [
            'model' => $publication,
            'observationOptions' => $observationOptions,
            'speciesOptions' => $this->getSpeciesOptions(),
        ]);
    }

    public function actionUpdate(int $id)
    {
        $publication = $this->publicationModelFromApi($id);
        $this->ensureManageAccess($publication);

        if ($publication->load(Yii::$app->request->post())) {
            try {
                Yii::$app->publicationApi->updatePublication($id, [
                    'title' => $publication->title,
                    'description' => $publication->description,
                    'status' => $publication->status ?: Publication::STATUS_PUBLISHED,
                ]);
                Yii::$app->session->setFlash('success', 'Publicacao atualizada com sucesso.');
                return $this->redirect(['publication/view', 'id' => $id]);
            } catch (RuntimeException $exception) {
                $publication->addError('description', 'Nao foi possivel atualizar a publicacao no backend: ' . $exception->getMessage());
            }
        }

        return $this->render('update', [
            'model' => $publication,
            'observationOptions' => $this->getEditableObservationOptions($publication->observation_id),
            'speciesOptions' => $this->getSpeciesOptions(),
        ]);
    }

    public function actionPublish(int $id)
    {
        $publication = $this->publicationModelFromApi($id);
        $this->ensureManageAccess($publication);

        try {
            Yii::$app->publicationApi->updatePublication($id, [
                'title' => $publication->title,
                'description' => $publication->description,
                'status' => Publication::STATUS_PUBLISHED,
            ]);
            Yii::$app->session->setFlash('success', 'Publicacao publicada com sucesso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel publicar no backend: ' . $exception->getMessage());
        }

        return $this->redirect(['publication/view', 'id' => $id]);
    }

    public function actionDelete(int $id)
    {
        $publication = $this->publicationModelFromApi($id);
        $this->ensureManageAccess($publication);

        try {
            Yii::$app->publicationApi->deletePublication($id);
            Yii::$app->session->setFlash('success', 'Publicacao removida com sucesso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel remover no backend: ' . $exception->getMessage());
        }
        return $this->redirect(['publication/index']);
    }

    private function getEditableObservationOptions(?int $currentObservationId = null): array
    {
        try {
            $result = Yii::$app->observationApi->listObservations('', 'all', false, 0, 1000);
            $observations = $result['items'];
        } catch (RuntimeException) {
            $observations = [];
        }

        $options = [];
        foreach ($observations as $observation) {
            if ($observation->is_published && (int) $observation->observation_id !== (int) $currentObservationId) {
                continue;
            }
            if (!(Yii::$app->user->identity?->isAdmin() ?? false) && (int) $observation->user_id !== (int) Yii::$app->user->id) {
                continue;
            }
            $options[$observation->observation_id] = sprintf(
                '#%d - %s - %s',
                $observation->observation_id,
                $observation->getResolvedCommonName() ?: 'Observacao botanica',
                Yii::$app->formatter->asDate($observation->observed_at, 'php:d/m/Y')
            );
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

    private function ensureManageAccess(Publication $publication): void
    {
        if (!$publication->canBeManagedBy(Yii::$app->user->identity)) {
            throw new ForbiddenHttpException('Nao tens permissao para gerir esta publicacao.');
        }
    }

    private function publicationModelFromApi(int $id): Publication
    {
        $apiPublication = Yii::$app->publicationApi->getPublicationById($id);
        if ($apiPublication === null) {
            throw new NotFoundHttpException('Publicacao nao encontrada.');
        }

        $publication = new Publication();
        $publication->publication_id = $apiPublication->publication_id;
        $publication->observation_id = $apiPublication->observation_id;
        $publication->user_id = $apiPublication->user_id ?? (int) Yii::$app->user->id;
        $publication->plant_species_id = $apiPublication->plant_species_id;
        $publication->title = $apiPublication->title;
        $publication->description = $apiPublication->description;
        $publication->status = $apiPublication->status ?: Publication::STATUS_PUBLISHED;
        $publication->published_at = $apiPublication->published_at;
        $publication->setIsNewRecord(false);
        return $publication;
    }
}
