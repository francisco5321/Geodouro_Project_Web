<?php

use app\models\PublicationForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var PublicationForm $model */
/** @var array $observationOptions */
/** @var array $speciesOptions */
?>
<div class="content-card publication-form-card">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'stacked-form']]); ?>
        <div class="auth-grid-two">
            <?= $form->field($model, 'observation_id')->dropDownList($observationOptions, ['prompt' => 'Seleciona uma observação']) ?>
            <?= $form->field($model, 'plant_species_id')->dropDownList($speciesOptions, ['prompt' => 'Herda automaticamente da observação']) ?>
        </div>
        <?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'Ex.: Flora primaveril junto ao percurso']) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 6, 'class' => 'form-control publication-description-textarea', 'placeholder' => 'Escreve o texto editorial, contexto botânico e observações relevantes.']) ?>
        <div class="form-action-row publication-form-actions">
            <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to($model->isNewRecord ? ['publication/index'] : ['publication/view', 'id' => $model->publication_id]) ?>">Cancelar</a>
            <?= Html::submitButton($model->isNewRecord ? 'Criar publicação' : 'Guardar alterações', ['class' => 'btn btn-brand btn-lg']) ?>
        </div>
    <?php ActiveForm::end(); ?>
</div>
