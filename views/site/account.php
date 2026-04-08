<?php

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
            <span class="eyebrow">Conta</span>
            <h1 class="hero-title hero-title-tight">Gerir perfil e credenciais</h1>
            <p class="hero-text">Atualiza os teus dados de acesso ao portal e mantém a conta alinhada com o resto da plataforma.</p>
            <div class="species-meta-row">
                <span class="species-meta-chip"><?= Html::encode($user->getFullName()) ?></span>
                <span class="species-meta-chip"><?= Html::encode($user->email ?: 'Sem email') ?></span>
                <span class="species-meta-chip"><?= Html::encode($user->getRoleLabel()) ?></span>
            </div>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Observacoes</span><strong><?= count($user->observations) ?></strong></article>
            <article class="detail-stat-card"><span>Publicacoes</span><strong><?= count($user->publications) ?></strong></article>
            <article class="detail-stat-card"><span>Username</span><strong><?= Html::encode($user->username ?: 'N/D') ?></strong></article>
            <article class="detail-stat-card"><span>Papel</span><strong><?= Html::encode($user->getRoleLabel()) ?></strong></article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <section class="detail-split-grid">
        <article class="content-card">
            <h2>Dados do perfil</h2>
            <?php $profile = ActiveForm::begin(); ?>
                <?= Html::hiddenInput('form_name', 'profile') ?>
                <div class="auth-grid-two">
                    <?= $profile->field($profileForm, 'first_name')->textInput() ?>
                    <?= $profile->field($profileForm, 'last_name')->textInput() ?>
                </div>
                <?= $profile->field($profileForm, 'email')->input('email') ?>
                <?= $profile->field($profileForm, 'username')->textInput() ?>
                <div class="d-grid auth-actions">
                    <?= Html::submitButton('Guardar perfil', ['class' => 'btn btn-brand']) ?>
                </div>
            <?php ActiveForm::end(); ?>
        </article>

        <article class="content-card content-card-soft">
            <h2>Seguranca</h2>
            <?php $password = ActiveForm::begin(); ?>
                <?= Html::hiddenInput('form_name', 'password') ?>
                <?= $password->field($passwordForm, 'currentPassword')->passwordInput() ?>
                <?= $password->field($passwordForm, 'newPassword')->passwordInput() ?>
                <?= $password->field($passwordForm, 'newPasswordRepeat')->passwordInput() ?>
                <div class="d-grid auth-actions">
                    <?= Html::submitButton('Atualizar password', ['class' => 'btn btn-outline-brand']) ?>
                </div>
            <?php ActiveForm::end(); ?>
            <?php if ($user->isAdmin()): ?>
                <div class="module-link-list mt-3">
                    <a href="<?= yii\helpers\Url::to(['user/index']) ?>">Abrir gestao de utilizadores</a>
                </div>
            <?php endif; ?>
        </article>
    </section>
</div>
