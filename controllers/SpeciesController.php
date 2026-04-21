<?php

namespace app\controllers;

use app\models\Observation;
use app\models\PlantSpecies;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SpeciesController extends Controller
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
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $queryText = trim((string) \Yii::$app->request->get('q', ''));
        $sort = (string) \Yii::$app->request->get('sort', 'species');
        $allowedSorts = ['species', 'family', 'genus'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'species';
        }

        $query = PlantSpecies::find();

        if ($queryText !== '') {
            $query->andWhere([
                'or',
                ['ilike', 'scientific_name', $queryText],
                ['ilike', 'common_name', $queryText],
                ['ilike', 'family', $queryText],
                ['ilike', 'genus', $queryText],
            ]);
        }

        $query->orderBy(match ($sort) {
            'family' => ['family' => SORT_ASC, 'scientific_name' => SORT_ASC],
            'genus' => ['genus' => SORT_ASC, 'scientific_name' => SORT_ASC],
            default => ['scientific_name' => SORT_ASC],
        });

        $pagination = new Pagination([
            'totalCount' => (clone $query)->count(),
            'pageSize' => 12,
        ]);

        $species = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $summary = [
            'speciesCount' => PlantSpecies::find()->count(),
            'observationsCount' => Observation::find()->count(),
            'familiesCount' => PlantSpecies::find()->select('family')->distinct()->count(),
        ];

        return $this->render('index', [
            'species' => $species,
            'pagination' => $pagination,
            'queryText' => $queryText,
            'sort' => $sort,
            'summary' => $summary,
        ]);
    }

    public function actionView(int $id): string
    {
        $species = PlantSpecies::findOne($id);

        if ($species === null) {
            throw new NotFoundHttpException('Especie nao encontrada.');
        }

        $observations = Observation::find()
            ->with(['user'])
            ->where(['plant_species_id' => $species->plant_species_id])
            ->orderBy(['observed_at' => SORT_DESC, 'observation_id' => SORT_DESC])
            ->limit(8)
            ->all();

        $stats = [
            'observationsCount' => Observation::find()->where(['plant_species_id' => $species->plant_species_id])->count(),
            'publishedCount' => Observation::find()->where([
                'plant_species_id' => $species->plant_species_id,
                'is_published' => true,
            ])->count(),
            'avgConfidence' => Observation::find()->where(['plant_species_id' => $species->plant_species_id])->average('confidence'),
        ];

        return $this->render('view', [
            'species' => $species,
            'observations' => $observations,
            'stats' => $stats,
        ]);
    }
}
