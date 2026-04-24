<?php

namespace app\controllers;

use app\models\Observation;
use app\models\Publication;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class MediaController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['observation-image', 'publication-image'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionObservationImage(int $id, int $index = 0): Response
    {
        $observation = Observation::find()
            ->with(['observationImages'])
            ->where(['observation_id' => $id])
            ->one();

        if ($observation === null) {
            throw new NotFoundHttpException('Observacao nao encontrada.');
        }

        return $this->sendRelativeUpload($observation->getImageGalleryPaths()[$index] ?? null);
    }

    public function actionPublicationImage(int $id, int $index = 0): Response
    {
        $publication = Publication::find()
            ->with(['publicationImages'])
            ->where(['publication_id' => $id])
            ->one();

        if ($publication === null) {
            throw new NotFoundHttpException('Publicacao nao encontrada.');
        }

        return $this->sendRelativeUpload($publication->getImageGalleryPaths()[$index] ?? null);
    }

    private function sendRelativeUpload(?string $relativePath): Response
    {
        if ($relativePath === null || trim($relativePath) === '') {
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

        $fileMTime = (int) filemtime($candidatePath);
        $etag = '"' . sha1($candidatePath . '|' . $fileMTime . '|' . filesize($candidatePath)) . '"';

        Yii::$app->response->headers
            ->set('Cache-Control', 'public, max-age=86400, stale-while-revalidate=604800')
            ->set('ETag', $etag)
            ->set('Last-Modified', gmdate('D, d M Y H:i:s', $fileMTime) . ' GMT');

        if (Yii::$app->request->headers->get('If-None-Match') === $etag) {
            Yii::$app->response->setStatusCode(304);
            return Yii::$app->response;
        }

        return Yii::$app->response->sendFile($candidatePath, basename($candidatePath), ['inline' => true]);
    }
}
