<?php

use app\models\Observation;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

/** @var yii\web\View $this */
/** @var Observation $model */
/** @var array $userOptions */
/** @var array $speciesOptions */

$isNewRecord = $model->isNewRecord;
$submitLabel = $isNewRecord ? 'Criar observacao' : 'Guardar alteracoes';
$cancelUrl = $isNewRecord ? ['map/index'] : ['observation/view', 'id' => $model->observation_id];
?>
<div class="content-card publication-form-card">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'stacked-form', 'enctype' => 'multipart/form-data']]); ?>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            Informacoes Basicas
        </legend>

        <div class="auth-grid-two">
            <?= $form->field($model, 'user_id')
                ->label($model->getAttributeLabel('user_id') . ' <span class="is-required">*</span>', ['encode' => false])
                ->dropDownList($userOptions, ['prompt' => 'Seleciona o autor']) ?>
            <?= $form->field($model, 'plant_species_id')
                ->label($model->getAttributeLabel('plant_species_id'), ['class' => 'form-label'])
                ->dropDownList($speciesOptions, ['prompt' => 'Sem especie associada']) ?>
        </div>

        <div>
            <?= $form->field($model, 'observed_at')
                ->label($model->getAttributeLabel('observed_at') . ' <span class="is-required">*</span>', ['encode' => false])
                ->input('datetime-local', ['class' => 'form-control']) ?>
            <small class="form-text">Data e hora em que a observacao foi feita</small>
        </div>
    </fieldset>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
            Localizacao Geografica
        </legend>

        <div class="auth-grid-two mb-3">
            <?= $form->field($model, 'latitude')
                ->label('Latitude' . ' <span class="is-required">*</span>', ['encode' => false])
                ->textInput(['type' => 'number', 'step' => '0.0000001', 'class' => 'form-control']) ?>
            <?= $form->field($model, 'longitude')
                ->label('Longitude' . ' <span class="is-required">*</span>', ['encode' => false])
                ->textInput(['type' => 'number', 'step' => '0.0000001', 'class' => 'form-control']) ?>
        </div>

    </fieldset>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-align-left" aria-hidden="true"></i>
            Descricao
        </legend>

        <div>
            <?= $form->field($model, 'notes')
                ->label('Descricao')
                ->textarea([
                    'rows' => 5,
                    'class' => 'form-control',
                    'placeholder' => 'Descreva a observacao, contexto e detalhes relevantes',
                    'data-auto-resize' => true,
                ])
                ->hint('Descricao e contexto da observacao') ?>
        </div>
    </fieldset>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-image" aria-hidden="true"></i>
            Imagem e Referencias
        </legend>

        <div>
            <?= Html::label('Imagem principal', 'observation-image-file', ['class' => 'form-label']) ?>
            <?= Html::fileInput('observation_image_file', null, [
                'id' => 'observation-image-file',
                'class' => 'form-control',
                'accept' => 'image/*',
            ]) ?>
            <?= Html::error($model, 'image_uri', ['class' => 'invalid-feedback d-block']) ?>
            <small class="form-text">Seleciona uma imagem do teu sistema de ficheiros</small>
            <?php if (!$model->isNewRecord && trim((string) $model->image_uri) !== ''): ?>
                <small class="form-text d-block">Imagem atual: <?= Html::encode(basename((string) $model->image_uri)) ?></small>
            <?php endif; ?>
        </div>

    </fieldset>

    <div class="form-action-row">
        <?= Html::submitButton(
            '<i class="fas fa-save" aria-hidden="true"></i> ' . $submitLabel,
            ['class' => 'btn btn-brand btn-lg', 'id' => 'submit-btn']
        ) ?>
        <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to($cancelUrl) ?>" title="Cancelar">
            <i class="fas fa-times" aria-hidden="true"></i>
            Cancelar
        </a>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<'JS'
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.stacked-form');
    const submitBtn = document.getElementById('submit-btn');
    if (form && submitBtn) {
        form.addEventListener('submit', () => {
            if (form.querySelectorAll('.is-invalid').length === 0) {
                UIHelpers.setButtonLoading(submitBtn);
            }
        });
    }

    document.querySelectorAll('textarea[data-auto-resize]').forEach(ta => {
        if (UIHelpers && UIHelpers.autoResizeTextarea) {
            UIHelpers.autoResizeTextarea(ta);
        }
    });
});
JS;

$this->registerJs($js);
?>

<style>
.form-section {
    border: 0;
    margin: 0;
    padding: 0;
}

.form-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 1.25rem 0;
    padding: 0 0 0.75rem 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--gf-text);
    border-bottom: 2px solid var(--gf-border);
}

.form-section-title i {
    color: var(--gf-brand);
    font-size: 1.2em;
}

.is-required {
    color: #dc3545;
    margin-left: 2px;
}
</style>
