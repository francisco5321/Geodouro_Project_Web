<?php

namespace app\controllers;

use app\models\ApiIdentity;
use app\services\ApiUser;
use RuntimeException;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class UserController extends Controller
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
                        'matchCallback' => static fn() => Yii::$app->user->identity?->isAdmin() ?? false,
                    ],
                ],
                'denyCallback' => static function () {
                    throw new \yii\web\ForbiddenHttpException('Apenas administradores podem aceder a esta area.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'set-role' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $search = mb_strtolower(trim((string) Yii::$app->request->get('q', '')));
        $pagination = new Pagination([
            'totalCount' => 0,
            'pageSize' => 10,
            'params' => array_merge(Yii::$app->request->get(), ['q' => $search]),
        ]);

        try {
            $users = array_map(
                static fn (array $user): ApiUser => ApiUser::fromArray($user),
                array_filter(Yii::$app->accountApi->listUsers(), 'is_array')
            );
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Não foi possível carregar utilizadores a partir da API.');
            $users = [];
        }

        if ($search !== '') {
            $users = array_values(array_filter($users, static function (ApiUser $user) use ($search): bool {
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $user->username,
                    $user->email,
                    $user->first_name,
                    $user->last_name,
                    $user->getFullName(),
                ])));
                return str_contains($haystack, $search);
            }));
        }

        usort($users, static fn (ApiUser $left, ApiUser $right): int => strcmp((string) $right->created_at, (string) $left->created_at));
        $pagination->totalCount = count($users);

        return $this->render('index', [
            'users' => array_slice($users, $pagination->offset, $pagination->limit),
            'pagination' => $pagination,
            'roleColumnAvailable' => true,
            'search' => $search,
        ]);
    }

    public function actionSetRole(int $id, string $role)
    {
        if (!in_array($role, [ApiIdentity::ROLE_USER, ApiIdentity::ROLE_ADMIN], true)) {
            throw new NotFoundHttpException('Role invalida.');
        }

        if ((int) $id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('success', 'Por seguranca, não podes alterar o teu proprio papel por aqui.');
            return $this->redirect(['user/index', 'q' => Yii::$app->request->post('q', '')]);
        }

        try {
            Yii::$app->accountApi->updateUserRole($id, $role);
            Yii::$app->session->setFlash('success', 'Papel do utilizador atualizado com sucesso.');
        } catch (RuntimeException $exception) {
            Yii::$app->session->setFlash('error', 'Não foi possível atualizar o papel no backend: ' . $exception->getMessage());
        }

        return $this->redirect(['user/index', 'q' => Yii::$app->request->post('q', '')]);
    }
}
