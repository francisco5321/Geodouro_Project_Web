<?php

namespace app\controllers;

use app\models\SpeciesForm;
use RuntimeException;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
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
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'roles' => ['@'],
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

        $pagination = new Pagination([
            'totalCount' => 0,
            'pageSize' => 10,
            'validatePage' => false,
        ]);

        try {
            $result = Yii::$app->speciesApi->listSpecies(
                $queryText,
                $sort,
                $pagination->getPage(),
                $pagination->getPageSize()
            );
        } catch (RuntimeException $exception) {
            if (Yii::$app->user->isGuest) {
                Yii::$app->session->setFlash('error', 'Tens de iniciar sessão para consultar o catálogo de espécies.');
                Yii::$app->user->setReturnUrl(Yii::$app->request->url);
                Yii::$app->user->loginRequired();
                return '';
            }

            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Não foi possível carregar as espécies a partir da API.');
            $result = [
                'items' => [],
                'totalCount' => 0,
                'summary' => ['speciesCount' => 0, 'observationsCount' => 0, 'familiesCount' => 0],
                'speciesImageMap' => [],
            ];
        }

        $pagination->totalCount = (int) $result['totalCount'];

        return $this->render('index', [
            'species' => $result['items'],
            'pagination' => $pagination,
            'queryText' => $queryText,
            'sort' => $sort,
            'summary' => $result['summary'],
            'speciesImageMap' => $result['speciesImageMap'],
        ]);
    }

    public function actionView(int $id): string
    {
        $pagination = new Pagination([
            'totalCount' => 0,
            'pageSize' => 5,
            'validatePage' => false,
        ]);

        try {
            $result = Yii::$app->speciesApi->getSpecies(
                $id,
                $pagination->getPage(),
                $pagination->getPageSize()
            );
        } catch (RuntimeException $exception) {
            if (Yii::$app->user->isGuest) {
                Yii::$app->session->setFlash('error', 'Tens de iniciar sessão para abrir o detalhe de uma espécie.');
                Yii::$app->user->setReturnUrl(Yii::$app->request->url);
                Yii::$app->user->loginRequired();
                return '';
            }

            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException('Espécie não encontrada na API.');
        }

        if ($result['species'] === null) {
            throw new NotFoundHttpException('Espécie não encontrada.');
        }

        $pagination->totalCount = (int) $result['totalCount'];

        return $this->render('view', [
            'species' => $result['species'],
            'observations' => $result['observations'],
            'pagination' => $pagination,
            'galleryImages' => $result['galleryImages'],
            'locationSummary' => $result['locationSummary'],
            'locationBounds' => $result['locationBounds'] ?? null,
            'locationPoints' => $result['locationPoints'] ?? [],
            'stats' => $result['stats'],
        ]);
    }

    public function actionUpdate(int $id)
    {
        $this->ensureAdminAccess();
        $model = $this->speciesFormFromApi($id);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                Yii::$app->speciesApi->updateSpecies($id, [
                    'scientificName' => $model->scientific_name,
                    'commonName' => $model->common_name,
                    'family' => $model->family,
                    'genus' => $model->genus,
                    'species' => $model->species,
                    'description' => $model->description,
                ]);
                Yii::$app->session->setFlash('success', 'Espécie atualizada com sucesso.');
                return $this->redirect(['species/view', 'id' => $id]);
            } catch (RuntimeException $exception) {
                $message = $exception->getMessage();
                $normalizedMessage = mb_strtolower($message);

                if (
                    str_contains($normalizedMessage, 'duplicate')
                    || str_contains($normalizedMessage, 'unique')
                    || str_contains($normalizedMessage, 'scientific_name')
                ) {
                    $model->addError('scientific_name', 'Já existe outra espécie com esse nome científico.');
                } else {
                    $model->addError('scientific_name', 'Não foi possível atualizar a espécie no backend: ' . $message);
                }
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    private function speciesFormFromApi(int $id): SpeciesForm
    {
        $result = Yii::$app->speciesApi->getSpecies($id, 0, 1);
        $apiSpecies = $result['species'] ?? null;
        if ($apiSpecies === null) {
            throw new NotFoundHttpException('Espécie não encontrada.');
        }

        $model = new SpeciesForm();
        $model->plant_species_id = (int) $apiSpecies->plant_species_id;
        $model->scientific_name = (string) $apiSpecies->scientific_name;
        $model->common_name = $apiSpecies->common_name;
        $model->family = (string) $apiSpecies->family;
        $model->genus = (string) $apiSpecies->genus;
        $model->species = (string) $apiSpecies->species;
        $model->description = $apiSpecies->description;
        $model->image_count = (int) $apiSpecies->image_count;
        return $model;
    }

    private function ensureAdminAccess(): void
    {
        if (!(Yii::$app->user->identity?->isAdmin() ?? false)) {
            throw new ForbiddenHttpException('Acesso reservado a administradores.');
        }
    }
}
