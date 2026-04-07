<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var app\models\SignupForm $model */

$this->title = 'Criar conta';
?>
<div class="auth-shell">
    <div class="login-panel signup-panel">
        <div class="login-copy">
            <span class="eyebrow">Criar conta</span>
            <h1>Junta-te ao portal GeoFlora</h1>
            <p>Cria uma conta autenticada para gerir observacoes, publicacoes e acompanhar o projeto na web.</p>
        </div>

        <?php $form = ActiveForm::begin(['options' => ['class' => 'signup-form']]); ?>
            <div class="auth-grid-two">
                <?= $form->field($model, 'first_name')->textInput(['autofocus' => true]) ?>
                <?= $form->field($model, 'last_name')->textInput() ?>
            </div>
            <?= $form->field($model, 'email')->input('email') ?>
            <?= $form->field($model, 'username')->textInput() ?>
            <div class="auth-grid-two">
                <?= $form->field($model, 'password')->passwordInput() ?>
                <?= $form->field($model, 'passwordRepeat')->passwordInput() ?>
            </div>
            <div class="d-grid auth-actions">
                <?= Html::submitButton('Criar conta', ['class' => 'btn btn-brand btn-lg']) ?>
            </div>
        <?php ActiveForm::end(); ?>

        <div class="auth-switch-note">
            <span>Ja tens conta?</span>
            <a href="<?= yii\helpers\Url::to(['site/login']) ?>">Entrar</a>
        </div>
    </div>
</div>
