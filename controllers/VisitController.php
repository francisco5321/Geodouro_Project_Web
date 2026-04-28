<?php

namespace app\controllers;

use RuntimeException;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class VisitController extends Controller
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
                    'toggle-species' => ['post'],
                    'toggle-publication' => ['post'],
                    'toggle-observation' => ['post'],
                    'create-route' => ['post'],
                    'remove' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): Response
    {
        return $this->redirect(['route-plan/index']);
    }

    public function actionToggleSpecies(int $id)
    {
        return $this->toggleTarget('species', $id);
    }

    public function actionTogglePublication(int $id)
    {
        return $this->toggleTarget('publication', $id);
    }

    public function actionToggleObservation(int $id): array|Response
    {
        try {
            $response = Yii::$app->visitTargetApi->toggle('observation', $id);
            $saved = (bool) ($response['saved'] ?? false);
            $message = $response['message'] ?? 'Lista Quero visitar atualizada.';
        } catch (RuntimeException $exception) {
            Yii::warning('Visit target backend toggle observation failed: ' . $exception->getMessage(), __METHOD__);
            $saved = false;
            $message = 'Nao foi possivel atualizar Quero visitar no backend comum: ' . $exception->getMessage();
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ['success' => false, 'saved' => false, 'message' => $message];
            }

            Yii::$app->session->setFlash('error', $message);
            return $this->redirect(Yii::$app->request->referrer ?: ['route-plan/index']);
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['success' => true, 'saved' => $saved, 'message' => $message];
        }

        Yii::$app->session->setFlash('success', $message);
        return $this->redirect(Yii::$app->request->referrer ?: ['route-plan/index']);
    }

    public function actionCreateRoute(): Response
    {
        $routePlanData = Yii::$app->request->post('RoutePlan', []);
        $name = trim((string) ($routePlanData['name'] ?? ''));
        $description = trim((string) ($routePlanData['description'] ?? ''));

        if ($name === '') {
            Yii::$app->session->setFlash('error', 'Define um nome para o percurso.');
            return $this->redirect(['route-plan/index']);
        }

        try {
            $targets = Yii::$app->visitTargetApi->listVisitTargets();
            $coordinateTargets = array_values(array_filter($targets, static function (array $target): bool {
                return isset($target['savedVisitTargetId'])
                    && $target['latitude'] !== null
                    && $target['longitude'] !== null;
            }));

            if ($coordinateTargets === []) {
                Yii::$app->session->setFlash('error', 'Primeiro tens de marcar pontos com coordenadas para gerar um percurso.');
                return $this->redirect(['route-plan/index']);
            }

            $response = Yii::$app->routePlanApi->createRoutePlan([
                'name' => $name,
                'description' => $description !== '' ? $description : null,
                'startLabel' => null,
                'startLatitude' => null,
                'startLongitude' => null,
            ]);

            $routePlanId = (int) ($response['routePlanId'] ?? 0);
            if ($routePlanId <= 0) {
                throw new RuntimeException('O backend nao devolveu o ID do percurso criado.');
            }

            foreach ($coordinateTargets as $target) {
                Yii::$app->routePlanApi->addTarget($routePlanId, (int) $target['savedVisitTargetId']);
            }

            Yii::$app->session->setFlash('success', 'Percurso criado com os pontos que escolheste no mapa.');
            return $this->redirect(['route-plan/view', 'id' => $routePlanId]);
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Nao foi possivel criar o percurso no backend: ' . $exception->getMessage());
            return $this->redirect(['route-plan/index']);
        }
    }

    public function actionRemove(int $id)
    {
        try {
            Yii::$app->visitTargetApi->remove($id);
            Yii::$app->session->setFlash('success', 'Alvo removido da tua lista de visita.');
        } catch (RuntimeException $exception) {
            Yii::warning('Visit target backend remove failed: ' . $exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Nao foi possivel remover este alvo no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect(['route-plan/index']);
    }

    private function toggleTarget(string $targetType, int $id): Response
    {
        try {
            $response = Yii::$app->visitTargetApi->toggle($targetType, $id);
            Yii::$app->session->setFlash('success', $response['message'] ?? 'Lista Quero visitar atualizada.');
        } catch (RuntimeException $exception) {
            Yii::warning("Visit target backend toggle {$targetType} failed: " . $exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Nao foi possivel atualizar Quero visitar no backend comum: ' . $exception->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['route-plan/index']);
    }
}
