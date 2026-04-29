<?php

use app\models\PlantSpecies;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var PlantSpecies $model */
?>
<div class="content-card publication-form-card">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'stacked-form']]); ?>

    <div class="auth-grid-two">
        <?= $form->field($model, 'scientific_name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'common_name')->textInput(['maxlength' => true]) ?>
    </div>

    <div class="auth-grid-two">
        <?= $form->field($model, 'family')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'genus')->textInput(['maxlength' => true]) ?>
    </div>

    <?= $form->field($model, 'species')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 6, 'class' => 'form-control publication-description-textarea']) ?>

    <div class="form-action-row publication-form-actions">
        <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to(['species/view', 'id' => $model->plant_species_id]) ?>">Cancelar</a>
        <?= Html::submitButton('Guardar alterações', ['class' => 'btn btn-brand btn-lg']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
