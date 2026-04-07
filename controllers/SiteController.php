<?php

namespace app\controllers;

use app\models\ChangePasswordForm;
use app\models\LoginForm;
use app\models\ProfileForm;
use app\models\SignupForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ErrorAction;

class SiteController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'account'],
                'rules' => [
                    [
                        'actions' => ['logout', 'account'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'speciesCount' => \app\models\PlantSpecies::find()->count(),
            'observationCount' => \app\models\Observation::find()->count(),
            'publicationCount' => \app\models\Publication::find()->count(),
            'userCount' => \app\models\AppUser::find()->where(['is_authenticated' => true])->count(),
        ]);
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            $user = $model->signup();
            if ($user !== null && Yii::$app->user->login($user, 0)) {
                Yii::$app->session->setFlash('success', 'Conta criada com sucesso. Bem-vindo ao portal.');
                return $this->goHome();
            }
        }

        $model->password = '';
        $model->passwordRepeat = '';
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionAccount()
    {
        /** @var \app\models\AppUser $user */
        $user = Yii::$app->user->identity;
        $profileForm = new ProfileForm($user);
        $passwordForm = new ChangePasswordForm($user);

        $request = Yii::$app->request;
        if ($request->isPost) {
            $formType = $request->post('form_name');

            if ($formType === 'profile' && $profileForm->load($request->post()) && $profileForm->save()) {
                Yii::$app->session->setFlash('success', 'Perfil atualizado com sucesso.');
                return $this->refresh();
            }

            if ($formType === 'password' && $passwordForm->load($request->post()) && $passwordForm->save()) {
                Yii::$app->session->setFlash('success', 'Password atualizada com sucesso.');
                return $this->refresh();
            }
        }

        return $this->render('account', [
            'profileForm' => $profileForm,
            'passwordForm' => $passwordForm,
            'user' => $user,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}

