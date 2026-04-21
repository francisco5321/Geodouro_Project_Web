<?php

namespace app\controllers;

use app\models\AppUser;
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
                    throw new \yii\web\ForbiddenHttpException('Apenas administradores podem aceder a esta área.');
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
        $search = trim((string) Yii::$app->request->get('q', ''));

        $query = AppUser::find()
            ->where(['is_authenticated' => true]);

        if ($search !== '') {
            $query->andWhere([
                'or',
                ['ilike', 'username', $search],
                ['ilike', 'email', $search],
                ['ilike', 'first_name', $search],
                ['ilike', 'last_name', $search],
                ['ilike', 'guest_label', $search],
            ]);
        }

        $query->orderBy(['created_at' => SORT_DESC, 'user_id' => SORT_DESC]);

        $pagination = new Pagination([
            'totalCount' => (clone $query)->count(),
            'pageSize' => 20,
            'params' => array_merge(Yii::$app->request->get(), ['q' => $search]),
        ]);

        $users = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'users' => $users,
            'pagination' => $pagination,
            'roleColumnAvailable' => (new AppUser())->hasAttribute('role'),
            'search' => $search,
        ]);
    }

    public function actionSetRole(int $id, string $role)
    {
        $user = AppUser::findOne(['user_id' => $id, 'is_authenticated' => true]);
        if ($user === null) {
            throw new NotFoundHttpException('Utilizador não encontrado.');
        }

        if (!$user->hasAttribute('role')) {
            Yii::$app->session->setFlash('success', 'A coluna de role ainda não existe nesta base de dados. Corre as migrations primeiro.');
            return $this->redirect(['user/index']);
        }

        if (!in_array($role, [AppUser::ROLE_USER, AppUser::ROLE_ADMIN], true)) {
            throw new NotFoundHttpException('Role invalida.');
        }

        if ((int) $user->user_id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('success', 'Por segurança, não podes alterar o teu próprio papel por aqui.');
            return $this->redirect(['user/index', 'q' => Yii::$app->request->post('q', '')]);
        }

        $user->role = $role;
        $user->save(false, ['role', 'updated_at']);
        Yii::$app->session->setFlash('success', 'Papel do utilizador atualizado com sucesso.');

        return $this->redirect(['user/index', 'q' => Yii::$app->request->post('q', '')]);
    }
}
