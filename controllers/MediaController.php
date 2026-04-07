<?php

namespace app\controllers;

use app\models\Observation;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MediaController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionObservationImage(int $id, int $index = 0): Response
    {
        $observation = Observation::find()
            ->with(['observationImages'])
            ->where(['observation_id' => $id])
            ->one();

        if ($observation === null) {
            throw new NotFoundHttpException('Observacao nao encontrada.');
        }

        $paths = $observation->getImageGalleryPaths();
        $relativePath = $paths[$index] ?? null;

        if ($relativePath === null) {
            throw new NotFoundHttpException('Imagem nao encontrada.');
        }

        $basePath = Yii::$app->params['backendUploadsPath'] ?? null;
        if (!$basePath) {
            throw new NotFoundHttpException('Diretorio de uploads nao configurado.');
        }

        $candidatePath = realpath($basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath));
        $resolvedBase = realpath($basePath);

        if ($candidatePath === false || $resolvedBase === false || !str_starts_with($candidatePath, $resolvedBase) || !is_file($candidatePath)) {
            throw new NotFoundHttpException('Ficheiro de imagem indisponivel.');
        }

        return Yii::$app->response->sendFile($candidatePath, basename($candidatePath), ['inline' => true]);
    }
}
