<?php

namespace app\controllers;

use app\models\Observation;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
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
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $status = trim((string) \Yii::$app->request->get('status', 'all'));
        $allowedStatuses = ['all', Observation::SYNC_PENDING, Observation::SYNC_SYNCED, Observation::SYNC_FAILED, 'PUBLISHED'];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $query = Observation::find()
            ->with(['user', 'plantSpecies'])
            ->orderBy(['observed_at' => SORT_DESC, 'observation_id' => SORT_DESC]);

        if ($status === 'PUBLISHED') {
            $query->andWhere(['is_published' => true]);
        } elseif ($status !== 'all') {
            $query->andWhere(['sync_status' => $status]);
        }

        $pagination = new Pagination([
            'totalCount' => (clone $query)->count(),
            'pageSize' => 12,
        ]);

        $observations = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $summary = [
            'total' => Observation::find()->count(),
            'published' => Observation::find()->where(['is_published' => true])->count(),
            'pending' => Observation::find()->where(['sync_status' => Observation::SYNC_PENDING])->count(),
            'failed' => Observation::find()->where(['sync_status' => Observation::SYNC_FAILED])->count(),
        ];

        return $this->render('index', [
            'observations' => $observations,
            'pagination' => $pagination,
            'status' => $status,
            'summary' => $summary,
        ]);
    }

    public function actionView(int $id): string
    {
        $observation = Observation::find()
            ->with(['user', 'plantSpecies', 'observationImages', 'publication'])
            ->where(['observation_id' => $id])
            ->one();

        if ($observation === null) {
            throw new NotFoundHttpException('Observacao nao encontrada.');
        }

        return $this->render('view', [
            'observation' => $observation,
        ]);
    }
}
