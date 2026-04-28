<?php

namespace app\controllers;

use RuntimeException;
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
                        'actions' => ['observation-image', 'publication-image', 'upload-path'],
                        'roles' => ['?', '@'],
                    ],
                ],
            ],
        ];
    }

    public function actionObservationImage(int $id, int $index = 0): Response
    {
        try {
            $observation = Yii::$app->observationApi->getObservationById($id);
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $observation = null;
        }

        if ($observation === null) {
            throw new NotFoundHttpException('Observação não encontrada.');
        }

        return $this->sendRelativeUpload($observation->getImageGalleryPaths()[$index] ?? null);
    }

    public function actionPublicationImage(int $id, int $index = 0): Response
    {
        try {
            $publication = Yii::$app->publicationApi->getPublicationById($id);
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $publication = null;
        }

        if ($publication === null) {
            throw new NotFoundHttpException('Publicação não encontrada.');
        }

        return $this->sendRelativeUpload($publication->getImageGalleryPaths()[$index] ?? null);
    }

    public function actionUploadPath(string $path): Response
    {
        return $this->sendRelativeUpload($path);
    }

    private function sendRelativeUpload(?string $relativePath): Response
    {
        if ($relativePath === null || trim($relativePath) === '') {
            throw new NotFoundHttpException('Imagem não encontrada.');
        }

        $basePaths = array_filter([
            Yii::$app->params['backendUploadsPath'] ?? null,
            Yii::getAlias('@webroot/uploads', false),
        ]);

        $candidatePath = null;
        foreach ($basePaths as $basePath) {
            $resolvedBase = realpath($basePath);
            $resolvedCandidate = realpath($basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath));

            if ($resolvedCandidate !== false && $resolvedBase !== false && str_starts_with($resolvedCandidate, $resolvedBase) && is_file($resolvedCandidate)) {
                $candidatePath = $resolvedCandidate;
                break;
            }
        }

        if ($candidatePath === null) {
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
