<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var app\models\RoutePlan $model */
?>
<div class="content-card publication-form-card">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'stacked-form']]); ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Ex.: Primavera no Douro']) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 5, 'placeholder' => 'Define o objetivo deste percurso e as plantas/publicacoes que queres priorizar.']) ?>

        <div class="form-action-row">
            <?= Html::submitButton($model->isNewRecord ? 'Criar percurso' : 'Guardar percurso', ['class' => 'btn btn-brand btn-lg']) ?>
            <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to($model->isNewRecord ? ['route-plan/index'] : ['route-plan/view', 'id' => $model->route_plan_id]) ?>">Cancelar</a>
        </div>
    <?php ActiveForm::end(); ?>
</div>