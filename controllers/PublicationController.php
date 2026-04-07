<?php

namespace app\controllers;

use app\models\Publication;
use yii\data\Pagination;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PublicationController extends Controller
{
    public function actionIndex(): string
    {
        $query = Publication::find()
            ->with(['user', 'plantSpecies', 'observation'])
            ->orderBy(['published_at' => SORT_DESC, 'publication_id' => SORT_DESC]);

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
            'species' => Publication::find()->select('plant_species_id')->where(['not', ['plant_species_id' => null]])->distinct()->count(),
            'authors' => Publication::find()->select('user_id')->distinct()->count(),
        ];

        return $this->render('index', [
            'publications' => $publications,
            'pagination' => $pagination,
            'summary' => $summary,
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
}
