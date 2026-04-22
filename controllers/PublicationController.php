<?php

namespace app\controllers;

use app\models\AppUser;
use app\models\Observation;
use app\models\PlantSpecies;
use app\models\Publication;
use Yii;
use yii\data\Pagination;
use yii\db\Expression;
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
        $identity = Yii::$app->user->identity;
        if ($identity === null && $scope === 'mine') {
            $scope = 'all';
        }

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

        $summaryRow = Publication::find()->select([
            'total' => new Expression('COUNT(*)'),
            'drafts' => new Expression('SUM(CASE WHEN status = :draft THEN 1 ELSE 0 END)', [
                ':draft' => Publication::STATUS_DRAFT,
            ]),
            'published' => new Expression('SUM(CASE WHEN status = :published THEN 1 ELSE 0 END)', [
                ':published' => Publication::STATUS_PUBLISHED,
            ]),
        ])->asArray()->one() ?: [];

        $summary = [
            'total' => (int) ($summaryRow['total'] ?? 0),
            'drafts' => (int) ($summaryRow['drafts'] ?? 0),
            'published' => (int) ($summaryRow['published'] ?? 0),
            'availableObservationCount' => $this->countEditableObservations(),
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
            throw new NotFoundHttpException('Publicação não encontrada.');
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
            Yii::$app->session->setFlash('success', 'Não há observações elegíveis para criar uma nova publicação.');
            return $this->redirect(['publication/index']);
        }

        if ($publication->load(Yii::$app->request->post()) && $this->persistPublication($publication, true)) {
            Yii::$app->session->setFlash('success', 'Publicação criada com sucesso.');
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
            Yii::$app->session->setFlash('success', 'Publicação atualizada com sucesso.');
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

        Yii::$app->session->setFlash('success', 'Publicação publicada com sucesso.');
        return $this->redirect(['publication/view', 'id' => $publication->publication_id]);
    }

    public function actionDelete(int $id)
    {
        $publication = $this->findModel($id);
        $this->ensureManageAccess($publication);

        $observationId = (int) $publication->observation_id;
        $publication->delete();
        $this->syncObservationPublicationState($observationId);

        Yii::$app->session->setFlash('success', 'Publicação removida com sucesso.');
        return $this->redirect(['publication/index']);
    }

    private function persistPublication(Publication $publication, bool $isNew): bool
    {
        $currentUser = Yii::$app->user->identity;
        $allowedObservationIds = array_keys($this->getEditableObservationOptions($isNew ? null : (int) $publication->getOldAttribute('observation_id')));
        $allowedObservationIds = array_map('intval', $allowedObservationIds);

        if (!in_array((int) $publication->observation_id, $allowedObservationIds, true)) {
            $publication->addError('observation_id', 'Esta observação não está disponível para esta publicação.');
            return false;
        }

        $observation = Observation::findOne((int) $publication->observation_id);
        if ($observation === null) {
            $publication->addError('observation_id', 'Observação não encontrada.');
            return false;
        }

        if (!$currentUser->isAdmin() && (int) $observation->user_id !== (int) $currentUser->user_id) {
            throw new ForbiddenHttpException('Só podes publicar observações tuas.');
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
        if ($currentUser === null) {
            return [];
        }

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
                $observation->getResolvedCommonName() ?: 'Observação botânica',
                Yii::$app->formatter->asDate($observation->observed_at, 'php:d/m/Y')
            );
            $options[$observation->observation_id] = $label;
        }

        return $options;
    }

    private function countEditableObservations(?int $currentObservationId = null): int
    {
        $currentUser = Yii::$app->user->identity;
        if ($currentUser === null) {
            return 0;
        }

        $publicationTable = Publication::tableName();
        $query = Observation::find()
            ->alias('o')
            ->leftJoin(['p' => $publicationTable], 'p.observation_id = o.observation_id')
            ->where(['or', ['p.publication_id' => null], ['o.observation_id' => $currentObservationId]]);

        if (!$currentUser->isAdmin()) {
            $query->andWhere(['o.user_id' => $currentUser->user_id]);
        }

        return (int) $query->count('DISTINCT o.observation_id');
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
            throw new ForbiddenHttpException('Não tens permissão para gerir esta publicação.');
        }
    }

    private function findModel(int $id): Publication
    {
        $publication = Publication::findOne(['publication_id' => $id]);
        if ($publication === null) {
            throw new NotFoundHttpException('Publicação não encontrada.');
        }

        return $publication;
    }
}
