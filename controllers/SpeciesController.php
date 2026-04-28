<?php

namespace app\controllers;

use RuntimeException;
use Yii;
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
        $queryText = trim((string) Yii::$app->request->get('q', ''));
        $sort = (string) Yii::$app->request->get('sort', 'species');
        $allowedSorts = ['species', 'family', 'genus'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'species';
        }

        $pagination = new Pagination([
            'totalCount' => 0,
            'pageSize' => 10,
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
            'stats' => $result['stats'],
        ]);
    }
}
