<?php

use app\models\Observation;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var Observation $model */
/** @var array $userOptions */
/** @var array $speciesOptions */
?>
<div class="content-card publication-form-card">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'stacked-form']]); ?>
        <div class="auth-grid-two">
            <?= $form->field($model, 'user_id')->dropDownList($userOptions, ['prompt' => 'Seleciona o autor']) ?>
            <?= $form->field($model, 'plant_species_id')->dropDownList($speciesOptions, ['prompt' => 'Sem especie associada']) ?>
        </div>
        <div class="auth-grid-two">
            <?= $form->field($model, 'latitude')->textInput(['type' => 'number', 'step' => '0.0000001']) ?>
            <?= $form->field($model, 'longitude')->textInput(['type' => 'number', 'step' => '0.0000001']) ?>
        </div>
        <div class="auth-grid-two">
            <?= $form->field($model, 'observed_at')->input('datetime-local') ?>
            <?= $form->field($model, 'captured_at')->textInput(['type' => 'number', 'step' => '1']) ?>
        </div>
        <div class="auth-grid-two">
            <?= $form->field($model, 'sync_status')->dropDownList([
                Observation::SYNC_PENDING => 'Pendente',
                Observation::SYNC_SYNCED => 'Sincronizada',
                Observation::SYNC_FAILED => 'Falhada',
            ]) ?>
            <?= $form->field($model, 'confidence')->textInput(['type' => 'number', 'step' => '0.01', 'min' => 0, 'max' => 1]) ?>
        </div>
        <div class="auth-grid-two">
            <?= $form->field($model, 'is_synced')->checkbox() ?>
            <?= $form->field($model, 'is_published')->checkbox() ?>
        </div>
        <div class="auth-grid-two">
            <?= $form->field($model, 'predicted_scientific_name')->textInput() ?>
            <?= $form->field($model, 'enriched_scientific_name')->textInput() ?>
        </div>
        <div class="auth-grid-two">
            <?= $form->field($model, 'enriched_common_name')->textInput() ?>
            <?= $form->field($model, 'enriched_family')->textInput() ?>
        </div>
        <?= $form->field($model, 'device_observation_id')->textInput(['placeholder' => 'UUID opcional']) ?>
        <?= $form->field($model, 'image_uri')->textInput(['placeholder' => 'Caminho ou URL da imagem principal']) ?>
        <?= $form->field($model, 'enriched_wikipedia_url')->textInput(['placeholder' => 'URL opcional']) ?>
        <?= $form->field($model, 'enriched_photo_url')->textInput(['placeholder' => 'URL opcional']) ?>
        <?= $form->field($model, 'notes')->textarea(['rows' => 5, 'placeholder' => 'Notas de campo e contexto da observacao']) ?>
        <div class="form-action-row">
            <?= Html::submitButton('Criar observacao', ['class' => 'btn btn-brand btn-lg']) ?>
            <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to(['map/index']) ?>">Cancelar</a>
        </div>
    <?php ActiveForm::end(); ?>
</div>
