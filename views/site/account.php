<?php

use app\components\StatCard;
use app\models\AppUser;
use app\models\ChangePasswordForm;
use app\models\ProfileForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var AppUser $user */
/** @var ProfileForm $profileForm */
/** @var ChangePasswordForm $passwordForm */

$this->title = 'A minha conta';
?>
<div class="module-shell">
    <section class="species-detail-hero mb-4">
        <div class="species-detail-copy">
            <span class="eyebrow">
                <i class="fas fa-user-circle" aria-hidden="true"></i>
                Conta
            </span>
            <h1 class="hero-title hero-title-tight">Gerir Perfil e Credenciais</h1>
            <p class="hero-text">Atualiza os teus dados de acesso ao portal e mantém a conta alinhada com o resto da plataforma.</p>
            <div class="species-meta-row">
                <span class="species-meta-chip"><?= Html::encode($user->getFullName()) ?></span>
                <span class="species-meta-chip"><?= Html::encode($user->email ?: 'Sem email') ?></span>
                <span class="species-meta-chip"><?= Html::encode($user->getRoleLabel()) ?></span>
            </div>
        </div>
        <div class="detail-stat-grid">
            <?= StatCard::widget([
                'label' => 'Observações',
                'value' => count($user->observations),
                'icon' => 'fas fa-eye',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Publicações',
                'value' => count($user->publications),
                'icon' => 'fas fa-file-alt',
            ]) ?>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert-success-custom mb-4">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <section class="detail-split-grid">
        <article class="content-card">
            <h2 class="section-title mb-3">
                <i class="fas fa-user" aria-hidden="true"></i>
                Dados do Perfil
            </h2>
            <?php $profile = ActiveForm::begin(); ?>
                <?= Html::hiddenInput('form_name', 'profile') ?>
                <div class="auth-grid-two">
                    <?= $profile->field($profileForm, 'first_name')->textInput() ?>
                    <?= $profile->field($profileForm, 'last_name')->textInput() ?>
                </div>
                <?= $profile->field($profileForm, 'email')->input('email') ?>
                <?= $profile->field($profileForm, 'username')->textInput() ?>
                <div class="d-grid auth-actions">
                    <?= Html::submitButton('Guardar Perfil', ['class' => 'btn btn-brand']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </article>

        <article class="content-card content-card-soft">
            <h2 class="section-title mb-3">
                <i class="fas fa-lock" aria-hidden="true"></i>
                Segurança
            </h2>
            <?php $password = ActiveForm::begin(); ?>
                <?= Html::hiddenInput('form_name', 'password') ?>
                <?= $password->field($passwordForm, 'currentPassword')->passwordInput() ?>
                <?= $password->field($passwordForm, 'newPassword')->passwordInput() ?>
                <?= $password->field($passwordForm, 'newPasswordRepeat')->passwordInput() ?>
                <div class="d-grid auth-actions">
                    <?= Html::submitButton('Atualizar Password', ['class' => 'btn btn-outline']) ?>
                </div>
            <?php ActiveForm::end(); ?>
            <?php if ($user->isAdmin()): ?>
                <div class="module-link-list mt-3">
                    <a href="<?= yii\helpers\Url::to(['user/index']) ?>">Abrir gestão de utilizadores</a>
                </div>
            <?php endif; ?>
        </article>
    </section>
</div>
