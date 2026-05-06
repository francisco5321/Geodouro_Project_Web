<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var app\models\RoutePlan $model */
?>
<div class="content-card publication-form-card">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'stacked-form']]); ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Ex.: Primavera no Douro']) ?>
        <?= $form->field($model, 'description')->textarea([
            'rows' => 5,
            'placeholder' => 'Define o objetivo deste percurso e as plantas/publicações que queres priorizar.',
            'style' => $model->isNewRecord ? null : 'resize: none;',
        ]) ?>

        <div class="form-action-row">
            <?php if ($model->isNewRecord): ?>
                <?= Html::submitButton('Criar percurso', ['class' => 'btn btn-brand btn-lg']) ?>
                <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to(['route-plan/index']) ?>">Cancelar</a>
            <?php else: ?>
                <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to(['route-plan/view', 'id' => $model->route_plan_id]) ?>">Cancelar</a>
                <?= Html::submitButton('Confirmar', ['class' => 'btn btn-brand btn-lg']) ?>
            <?php endif; ?>
        </div>
    <?php ActiveForm::end(); ?>
</div>
