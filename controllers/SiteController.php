<?php

namespace app\controllers;

use app\models\ChangePasswordForm;
use app\models\LoginForm;
use app\models\ProfileForm;
use app\models\SignupForm;
use RuntimeException;
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
        try {
            $identity = Yii::$app->user->identity;
            $cacheScope = Yii::$app->user->isGuest
                ? 'guest'
                : (($identity?->isAdmin() ?? false) ? 'admin' : 'user-' . (string) Yii::$app->user->id);

            $counts = Yii::$app->cache->getOrSet('dashboard.stats.api.v2.' . $cacheScope, static function (): array {
                $dashboardStats = Yii::$app->dashboardApi->getStats();
                $speciesSummary = Yii::$app->speciesApi->listSpecies('', 'species', 0, 1)['summary'] ?? [];
                $observationSummary = Yii::$app->observationApi->listObservations('', 'all', false, 0, 1)['summary'] ?? [];

                return [
                    'speciesCount' => (int) ($speciesSummary['speciesCount'] ?? 0),
                    'observationCount' => (int) ($observationSummary['total'] ?? 0),
                    'manualReviewCount' => (int) ($observationSummary['manualReview'] ?? 0),
                    'publicationCount' => (int) ($dashboardStats['publicationCount'] ?? 0),
                    'userCount' => (int) ($dashboardStats['userCount'] ?? 0),
                ];
            }, 60);
        } catch (RuntimeException $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $counts = [
                'speciesCount' => 0,
                'observationCount' => 0,
                'manualReviewCount' => 0,
                'publicationCount' => 0,
                'userCount' => 0,
            ];
        }

        return $this->render('index', [
            'speciesCount' => $counts['speciesCount'],
            'observationCount' => $counts['observationCount'],
            'manualReviewCount' => $counts['manualReviewCount'],
            'publicationCount' => $counts['publicationCount'],
            'userCount' => $counts['userCount'],
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
        Yii::$app->backendAuthSession->clear();
        Yii::$app->user->logout();
        return $this->goHome();
    }
}
