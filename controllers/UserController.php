<?php

namespace app\controllers;

use app\models\AppUser;
use Yii;
use yii\data\Pagination;
use yii\filters\AccessControl;
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
        ];
    }

    public function actionIndex(): string
    {
        $query = AppUser::find()
            ->where(['is_authenticated' => true])
            ->orderBy(['created_at' => SORT_DESC, 'user_id' => SORT_DESC]);

        $pagination = new Pagination([
            'totalCount' => (clone $query)->count(),
            'pageSize' => 20,
        ]);

        $users = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'users' => $users,
            'pagination' => $pagination,
            'roleColumnAvailable' => (new AppUser())->hasAttribute('role'),
        ]);
    }

    public function actionSetRole(int $id, string $role)
    {
        $user = AppUser::findOne(['user_id' => $id, 'is_authenticated' => true]);
        if ($user === null) {
            throw new NotFoundHttpException('Utilizador nao encontrado.');
        }

        if (!$user->hasAttribute('role')) {
            Yii::$app->session->setFlash('success', 'A coluna de role ainda nao existe nesta base de dados. Corre as migrations primeiro.');
            return $this->redirect(['user/index']);
        }

        if (!in_array($role, [AppUser::ROLE_USER, AppUser::ROLE_ADMIN], true)) {
            throw new NotFoundHttpException('Role invalida.');
        }

        if ((int) $user->user_id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('success', 'Por seguranca, nao podes alterar o teu proprio papel por aqui.');
            return $this->redirect(['user/index']);
        }

        $user->role = $role;
        $user->save(false, ['role', 'updated_at']);
        Yii::$app->session->setFlash('success', 'Papel do utilizador atualizado com sucesso.');

        return $this->redirect(['user/index']);
    }
}
