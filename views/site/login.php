<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

$this->title = 'Entrar';
?>
<div class="auth-shell">
    <div class="login-panel">
        <div class="login-copy">
            <span class="eyebrow">Acesso reservado</span>
            <h1>Entrar no portal GeoFlora</h1>
            <p>Usa as tuas credenciais para aceder aos módulos de espécies, observações, publicações e mapa.</p>
        </div>

        <?php $form = ActiveForm::begin(['options' => ['class' => 'login-form']]); ?>
            <?= $form->field($model, 'username')->textInput([
                'autofocus' => true,
                'placeholder' => 'Username ou email',
            ]) ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'rememberMe')->checkbox() ?>
            <div class="d-grid auth-actions">
                <?= Html::submitButton('Entrar', ['class' => 'btn btn-brand btn-lg']) ?>
            </div>
        <?php ActiveForm::end(); ?>

        <div class="auth-switch-note">
            <span>Ainda não tens conta?</span>
            <a href="<?= yii\helpers\Url::to(['site/signup']) ?>">Criar conta</a>
        </div>
    </div>
</div>
