<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

$this->title = 'Entrar';
?>
<div class="login-shell">
    <div class="login-panel">
        <div class="login-copy">
            <span class="eyebrow">Acesso reservado</span>
            <h1>Entrar no portal GeoFlora</h1>
            <p>Utiliza credenciais de um utilizador autenticado existente na mesma base de dados do projeto mobile.</p>
        </div>

        <?php $form = ActiveForm::begin(['options' => ['class' => 'login-form']]); ?>
            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'rememberMe')->checkbox() ?>
            <div class="d-grid">
                <?= Html::submitButton('Entrar', ['class' => 'btn btn-brand btn-lg']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
