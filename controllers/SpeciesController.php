<?php

namespace app\controllers;

use app\models\Observation;
use app\models\PlantSpecies;
use app\models\Publication;
use yii\data\Pagination;
use yii\db\Expression;
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

        $activeSpeciesQuery = PlantSpecies::find()
            ->alias('species')
            ->andWhere([
                'or',
                ['exists', Observation::find()
                    ->alias('observation')
                    ->select(new Expression('1'))
                    ->where('observation.plant_species_id = species.plant_species_id')],
                ['exists', Publication::find()
                    ->alias('publication')
                    ->select(new Expression('1'))
                    ->where('publication.plant_species_id = species.plant_species_id')],
            ]);

        $query = clone $activeSpeciesQuery;

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
            'pageSize' => 10,
        ]);

        $species = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $speciesImageMap = [];
        $speciesIds = array_map(static fn (PlantSpecies $item): int => (int) $item->plant_species_id, $species);
        if (!empty($speciesIds)) {
            $imageCandidatesBySpecies = [];
            $observationsWithImages = Observation::find()
                ->with(['observationImages'])
                ->where(['plant_species_id' => $speciesIds])
                ->all();

            foreach ($observationsWithImages as $observation) {
                $imagePaths = $observation->getImageGalleryPaths();
                if (empty($imagePaths) || $observation->plant_species_id === null) {
                    continue;
                }

                $imageCandidatesBySpecies[(int) $observation->plant_species_id][] = [
                    'observationId' => (int) $observation->observation_id,
                    'imageIndex' => random_int(0, count($imagePaths) - 1),
                ];
            }

            foreach ($imageCandidatesBySpecies as $speciesId => $candidates) {
                $speciesImageMap[$speciesId] = $candidates[array_rand($candidates)];
            }
        }

        $summary = [
            'speciesCount' => (clone $activeSpeciesQuery)->limit(-1)->offset(-1)->orderBy([])->count(),
            'observationsCount' => Observation::find()->count(),
            'familiesCount' => (clone $activeSpeciesQuery)->limit(-1)->offset(-1)->orderBy([])->select('family')->distinct()->count(),
        ];

        return $this->render('index', [
            'species' => $species,
            'pagination' => $pagination,
            'queryText' => $queryText,
            'sort' => $sort,
            'summary' => $summary,
            'speciesImageMap' => $speciesImageMap,
        ]);
    }

    public function actionView(int $id): string
    {
        $species = PlantSpecies::findOne($id);

        if ($species === null) {
            throw new NotFoundHttpException('Espécie não encontrada.');
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
