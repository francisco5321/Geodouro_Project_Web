<?php

namespace app\controllers;

use app\models\Observation;
use app\models\ObservationImage;
use app\models\PlantSpecies;
use app\models\Publication;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
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
        $queryText = trim((string) Yii::$app->request->get('q', ''));
        $sort = (string) Yii::$app->request->get('sort', 'species');
        $allowedSorts = ['species', 'family', 'genus'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'species';
        }

        $activeSpeciesQuery = $this->buildActiveSpeciesQuery();
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

        $speciesIds = array_map(static fn (PlantSpecies $item): int => (int) $item->plant_species_id, $species);

        $summary = Yii::$app->cache->getOrSet('species.index.summary.v2', function () use ($activeSpeciesQuery): array {
            return [
                'speciesCount' => (int) (clone $activeSpeciesQuery)->limit(-1)->offset(-1)->orderBy([])->count(),
                'observationsCount' => (int) Observation::find()->count(),
                'familiesCount' => (int) (clone $activeSpeciesQuery)->limit(-1)->offset(-1)->orderBy([])->select('family')->distinct()->count(),
            ];
        }, 300);

        return $this->render('index', [
            'species' => $species,
            'pagination' => $pagination,
            'queryText' => $queryText,
            'sort' => $sort,
            'summary' => $summary,
            'speciesImageMap' => $this->buildSpeciesImageMap($speciesIds),
        ]);
    }

    public function actionView(int $id): string
    {
        $species = PlantSpecies::findOne($id);

        if ($species === null) {
            throw new NotFoundHttpException('Especie nao encontrada.');
        }

        $observationQuery = Observation::find()
            ->with(['user', 'publication.user', 'observationImages'])
            ->where(['plant_species_id' => $species->plant_species_id])
            ->orderBy(['observed_at' => SORT_DESC, 'observation_id' => SORT_DESC]);

        $pagination = new Pagination([
            'totalCount' => (clone $observationQuery)->count(),
            'pageSize' => 5,
        ]);

        $observations = $observationQuery
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        $cachePrefix = 'species.view.' . (int) $species->plant_species_id . '.v2';
        $meta = Yii::$app->cache->getOrSet($cachePrefix . '.meta', function () use ($species): array {
            $aggregateRow = Observation::find()
                ->select([
                    'observationsCount' => new Expression('COUNT(*)'),
                    'publishedCount' => new Expression('SUM(CASE WHEN is_published THEN 1 ELSE 0 END)'),
                    'syncedCount' => new Expression('SUM(CASE WHEN sync_status = :synced THEN 1 ELSE 0 END)', [
                        ':synced' => Observation::SYNC_SYNCED,
                    ]),
                    'avgConfidence' => new Expression('AVG(confidence)'),
                    'locationCount' => new Expression('SUM(CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN 1 ELSE 0 END)'),
                    'minLatitude' => new Expression('MIN(latitude)'),
                    'minLongitude' => new Expression('MIN(longitude)'),
                    'maxLatitude' => new Expression('MAX(latitude)'),
                    'maxLongitude' => new Expression('MAX(longitude)'),
                ])
                ->where(['plant_species_id' => $species->plant_species_id])
                ->asArray()
                ->one() ?: [];

            $locationSummary = null;
            $locationCount = (int) ($aggregateRow['locationCount'] ?? 0);
            if ($locationCount > 0) {
                $locationSummary = sprintf(
                    'Localizacoes registadas em %d observacoes. Intervalo aproximado: %.4f, %.4f ate %.4f, %.4f.',
                    $locationCount,
                    (float) $aggregateRow['minLatitude'],
                    (float) $aggregateRow['minLongitude'],
                    (float) $aggregateRow['maxLatitude'],
                    (float) $aggregateRow['maxLongitude']
                );
            }

            return [
                'locationSummary' => $locationSummary,
                'stats' => [
                    'observationsCount' => (int) ($aggregateRow['observationsCount'] ?? 0),
                    'publishedCount' => (int) ($aggregateRow['publishedCount'] ?? 0),
                    'syncedCount' => (int) ($aggregateRow['syncedCount'] ?? 0),
                    'avgConfidence' => $aggregateRow['avgConfidence'] !== null ? (float) $aggregateRow['avgConfidence'] : null,
                ],
            ];
        }, 300);

        $galleryImages = Yii::$app->cache->getOrSet($cachePrefix . '.gallery', function () use ($species): array {
            $imageObservations = Observation::find()
                ->with(['observationImages'])
                ->where(['plant_species_id' => $species->plant_species_id])
                ->orderBy(['observed_at' => SORT_DESC, 'observation_id' => SORT_DESC])
                ->limit(12)
                ->all();

            $galleryImages = [];
            foreach ($imageObservations as $imageObservation) {
                foreach ($imageObservation->getImageGalleryPaths() as $index => $path) {
                    $galleryImages[] = [
                        'observationId' => (int) $imageObservation->observation_id,
                        'imageIndex' => (int) $index,
                    ];
                }
            }

            return array_values(array_slice($galleryImages, 0, 12));
        }, 300);

        return $this->render('view', [
            'species' => $species,
            'observations' => $observations,
            'pagination' => $pagination,
            'galleryImages' => $galleryImages,
            'locationSummary' => $meta['locationSummary'],
            'stats' => $meta['stats'],
        ]);
    }

    private function buildActiveSpeciesQuery(): ActiveQuery
    {
        return PlantSpecies::find()
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
    }

    /**
     * @param int[] $speciesIds
     * @return array<int, array{observationId:int, imageIndex:int}>
     */
    private function buildSpeciesImageMap(array $speciesIds): array
    {
        if ($speciesIds === []) {
            return [];
        }

        sort($speciesIds);
        $cacheKey = 'species.index.image-map.' . sha1((string) json_encode($speciesIds));

        return Yii::$app->cache->getOrSet($cacheKey, function () use ($speciesIds): array {
            $rows = Observation::find()
                ->alias('observation')
                ->select([
                    'observation.observation_id',
                    'observation.plant_species_id',
                    'imageCount' => new Expression('COUNT(observation_image.observation_image_id)'),
                ])
                ->innerJoin(['observation_image' => ObservationImage::tableName()], 'observation_image.observation_id = observation.observation_id')
                ->where(['observation.plant_species_id' => $speciesIds])
                ->groupBy(['observation.observation_id', 'observation.plant_species_id'])
                ->orderBy(['observation.observed_at' => SORT_DESC, 'observation.observation_id' => SORT_DESC])
                ->asArray()
                ->all();

            $map = [];
            foreach ($rows as $row) {
                $speciesId = (int) $row['plant_species_id'];
                if (isset($map[$speciesId])) {
                    continue;
                }

                $map[$speciesId] = [
                    'observationId' => (int) $row['observation_id'],
                    'imageIndex' => 0,
                ];
            }

            return $map;
        }, 300);
    }
}
