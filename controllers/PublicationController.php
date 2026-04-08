<?php

namespace app\controllers;

use app\models\AppUser;
use app\models\Observation;
use app\models\PlantSpecies;
use app\models\Publication;
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
        $identity = Yii::$app->user->identity;
        $query = Publication::find()
            ->with(['user', 'plantSpecies', 'observation', 'publicationImages'])
            ->orderBy(['published_at' => SORT_DESC, 'publication_id' => SORT_DESC]);

        if ($scope === 'mine' && $identity !== null) {
            $query->andWhere(['user_id' => $identity->user_id]);
        }

        $pagination = new Pagination([
            'totalCount' => (clone $query)->count(),
            'pageSize' => 12,
        ]);

        $publications = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $summary = [
            'total' => Publication::find()->count(),
            'drafts' => Publication::find()->where(['status' => Publication::STATUS_DRAFT])->count(),
            'published' => Publication::find()->where(['status' => Publication::STATUS_PUBLISHED])->count(),
            'availableObservationCount' => count($this->getEditableObservationOptions()),
        ];

        return $this->render('index', [
            'publications' => $publications,
            'pagination' => $pagination,
            'summary' => $summary,
            'scope' => $scope,
        ]);
    }

    public function actionView(int $id): string
    {
        $publication = Publication::find()
            ->with(['user', 'plantSpecies', 'observation', 'publicationImages'])
            ->where(['publication_id' => $id])
            ->one();

        if ($publication === null) {
            throw new NotFoundHttpException('Publicacao nao encontrada.');
        }

        return $this->render('view', [
            'publication' => $publication,
        ]);
    }

    public function actionCreate(?int $observationId = null)
    {
        $publication = new Publication();
        $publication->status = Publication::STATUS_DRAFT;
        $publication->user_id = (int) Yii::$app->user->id;
        if ($observationId !== null) {
            $publication->observation_id = $observationId;
        }

        $speciesOptions = $this->getSpeciesOptions();
        $observationOptions = $this->getEditableObservationOptions($publication->observation_id ?: null);

        if (empty($observationOptions)) {
            Yii::$app->session->setFlash('success', 'Nao ha observacoes elegiveis para criar uma nova publicacao.');
            return $this->redirect(['publication/index']);
        }

        if ($publication->load(Yii::$app->request->post()) && $this->persistPublication($publication, true)) {
            Yii::$app->session->setFlash('success', 'Publicacao criada com sucesso.');
            return $this->redirect(['publication/view', 'id' => $publication->publication_id]);
        }

        return $this->render('create', [
            'model' => $publication,
            'observationOptions' => $observationOptions,
            'speciesOptions' => $speciesOptions,
        ]);
    }

    public function actionUpdate(int $id)
    {
        $publication = $this->findModel($id);
        $this->ensureManageAccess($publication);

        $speciesOptions = $this->getSpeciesOptions();
        $observationOptions = $this->getEditableObservationOptions($publication->observation_id);

        if ($publication->load(Yii::$app->request->post()) && $this->persistPublication($publication, false)) {
            Yii::$app->session->setFlash('success', 'Publicacao atualizada com sucesso.');
            return $this->redirect(['publication/view', 'id' => $publication->publication_id]);
        }

        return $this->render('update', [
            'model' => $publication,
            'observationOptions' => $observationOptions,
            'speciesOptions' => $speciesOptions,
        ]);
    }

    public function actionPublish(int $id)
    {
        $publication = $this->findModel($id);
        $this->ensureManageAccess($publication);

        $publication->status = Publication::STATUS_PUBLISHED;
        $publication->published_at = date('Y-m-d H:i:s');
        $publication->save(false, ['status', 'published_at', 'updated_at']);
        $this->syncObservationPublicationState($publication->observation_id);

        Yii::$app->session->setFlash('success', 'Publicacao publicada com sucesso.');
        return $this->redirect(['publication/view', 'id' => $publication->publication_id]);
    }

    public function actionDelete(int $id)
    {
        $publication = $this->findModel($id);
        $this->ensureManageAccess($publication);

        $observationId = (int) $publication->observation_id;
        $publication->delete();
        $this->syncObservationPublicationState($observationId);

        Yii::$app->session->setFlash('success', 'Publicacao removida com sucesso.');
        return $this->redirect(['publication/index']);
    }

    private function persistPublication(Publication $publication, bool $isNew): bool
    {
        $currentUser = Yii::$app->user->identity;
        $allowedObservationIds = array_keys($this->getEditableObservationOptions($isNew ? null : (int) $publication->getOldAttribute('observation_id')));
        $allowedObservationIds = array_map('intval', $allowedObservationIds);

        if (!in_array((int) $publication->observation_id, $allowedObservationIds, true)) {
            $publication->addError('observation_id', 'Esta observacao nao esta disponivel para esta publicacao.');
            return false;
        }

        $observation = Observation::findOne((int) $publication->observation_id);
        if ($observation === null) {
            $publication->addError('observation_id', 'Observacao nao encontrada.');
            return false;
        }

        if (!$currentUser->isAdmin() && (int) $observation->user_id !== (int) $currentUser->user_id) {
            throw new ForbiddenHttpException('So podes publicar observacoes tuas.');
        }

        $oldObservationId = $isNew ? null : (int) $publication->getOldAttribute('observation_id');
        $publication->user_id = $isNew ? (int) $currentUser->user_id : (int) $publication->user_id;
        if ($publication->plant_species_id === null && $observation->plant_species_id !== null) {
            $publication->plant_species_id = (int) $observation->plant_species_id;
        }
        if ($publication->status === Publication::STATUS_PUBLISHED) {
            $publication->published_at = date('Y-m-d H:i:s');
        }

        if (!$publication->save()) {
            return false;
        }

        if ($oldObservationId !== null && $oldObservationId !== (int) $publication->observation_id) {
            $this->syncObservationPublicationState($oldObservationId);
        }
        $this->syncObservationPublicationState((int) $publication->observation_id);

        return true;
    }

    private function getEditableObservationOptions(?int $currentObservationId = null): array
    {
        $currentUser = Yii::$app->user->identity;
        $observationTable = Observation::tableName();
        $publicationTable = Publication::tableName();

        $query = Observation::find()
            ->alias('o')
            ->with(['plantSpecies', 'publication'])
            ->leftJoin(['p' => $publicationTable], 'p.observation_id = o.observation_id')
            ->where(['or', ['p.publication_id' => null], ['o.observation_id' => $currentObservationId]])
            ->orderBy(['o.observed_at' => SORT_DESC, 'o.observation_id' => SORT_DESC]);

        if (!$currentUser->isAdmin()) {
            $query->andWhere(['o.user_id' => $currentUser->user_id]);
        }

        $observations = $query->all();
        $options = [];
        foreach ($observations as $observation) {
            $label = sprintf(
                '#%d - %s - %s',
                $observation->observation_id,
                $observation->getResolvedCommonName() ?: 'Observacao botanica',
                Yii::$app->formatter->asDate($observation->observed_at, 'php:d/m/Y')
            );
            $options[$observation->observation_id] = $label;
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

    private function syncObservationPublicationState(int $observationId): void
    {
        $observation = Observation::findOne($observationId);
        if ($observation === null) {
            return;
        }

        $isPublished = Publication::find()
            ->where([
                'observation_id' => $observationId,
                'status' => Publication::STATUS_PUBLISHED,
            ])
            ->exists();

        $observation->is_published = $isPublished;
        $observation->save(false, ['is_published', 'updated_at']);
    }

    private function ensureManageAccess(Publication $publication): void
    {
        if (!$publication->canBeManagedBy(Yii::$app->user->identity)) {
            throw new ForbiddenHttpException('Nao tens permissao para gerir esta publicacao.');
        }
    }

    private function findModel(int $id): Publication
    {
        $publication = Publication::findOne(['publication_id' => $id]);
        if ($publication === null) {
            throw new NotFoundHttpException('Publicacao nao encontrada.');
        }

        return $publication;
    }
}
