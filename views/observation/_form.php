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
    <?php $form = ActiveForm::begin(['options' => ['class' => 'stacked-form']]); ?>

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

        <button type="button" class="btn btn-outline-brand btn-sm" id="get-location-btn">
            <i class="fas fa-crosshairs" aria-hidden="true"></i>
            Obter localizacao atual
        </button>
        <small class="form-text d-block mt-2">Use GPS do seu dispositivo para preencher as coordenadas</small>
    </fieldset>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-microscope" aria-hidden="true"></i>
            Detalhes da Observacao
        </legend>

        <div class="auth-grid-two">
            <?= $form->field($model, 'confidence')
                ->label('Confianca (' . '<span class="confidence-value">0</span>%)', ['encode' => false])
                ->textInput([
                    'type' => 'range',
                    'step' => '0.01',
                    'min' => 0,
                    'max' => 1,
                    'class' => 'form-range',
                    'id' => 'confidence-range',
                ])->hint('Nivel de confianca da identificacao de 0 a 100%') ?>
            <?= $form->field($model, 'captured_at')
                ->label('Timestamp do dispositivo')
                ->textInput(['type' => 'number', 'step' => '1', 'class' => 'form-control'])
                ->hint('Segundos desde epoch (1970-01-01)') ?>
        </div>

        <div>
            <?= $form->field($model, 'notes')
                ->label('Notas de campo')
                ->textarea([
                    'rows' => 5,
                    'class' => 'form-control',
                    'placeholder' => 'Descreva a observacao, contexto e detalhes relevantes',
                    'data-auto-resize' => true,
                ])
                ->hint('Informacoes adicionais e contexto da observacao') ?>
        </div>
    </fieldset>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-tag" aria-hidden="true"></i>
            Classificacao da Especie
        </legend>

        <div class="auth-grid-two">
            <?= $form->field($model, 'predicted_scientific_name')
                ->label('Nome cientifico predito')
                ->textInput(['class' => 'form-control'])
                ->hint('Resultado da classificacao automatica') ?>
            <?= $form->field($model, 'enriched_scientific_name')
                ->label('Nome cientifico enriquecido')
                ->textInput(['class' => 'form-control'])
                ->hint('Dados validados do backend') ?>
        </div>

        <div class="auth-grid-two">
            <?= $form->field($model, 'enriched_common_name')
                ->label('Nome comum')
                ->textInput(['class' => 'form-control'])
                ->hint('Nome vernacular da especie') ?>
            <?= $form->field($model, 'enriched_family')
                ->label('Familia')
                ->textInput(['class' => 'form-control'])
                ->hint('Familia botanica') ?>
        </div>
    </fieldset>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-image" aria-hidden="true"></i>
            Imagem e Referencias
        </legend>

        <div>
            <?= $form->field($model, 'image_uri')
                ->label('Imagem principal')
                ->textInput(['class' => 'form-control', 'placeholder' => 'Caminho ou URL da imagem'])
                ->hint('URL ou caminho relativo para a imagem principal da observacao') ?>
        </div>

        <div class="auth-grid-two">
            <?= $form->field($model, 'enriched_wikipedia_url')
                ->label('Referencia Wikipedia')
                ->textInput(['class' => 'form-control', 'type' => 'url', 'placeholder' => 'https://pt.wikipedia.org/wiki/...'])
                ->hint('Link para Wikipedia sobre a especie') ?>
            <?= $form->field($model, 'enriched_photo_url')
                ->label('Referencia de foto')
                ->textInput(['class' => 'form-control', 'type' => 'url', 'placeholder' => 'https://...'])
                ->hint('Link para foto de referencia') ?>
        </div>
    </fieldset>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-sync-alt" aria-hidden="true"></i>
            Status de Sincronizacao
        </legend>

        <div class="auth-grid-two">
            <?= $form->field($model, 'sync_status')
                ->label('Status de sincronizacao')
                ->dropDownList([
                    Observation::SYNC_PENDING => 'Pendente',
                    Observation::SYNC_SYNCED => 'Sincronizada',
                    Observation::SYNC_FAILED => 'Falhada',
                ])
                ->hint('Estado de sincronizacao com backend') ?>
            <?= $form->field($model, 'device_observation_id')
                ->label('ID do dispositivo')
                ->textInput(['class' => 'form-control', 'placeholder' => 'UUID opcional'])
                ->hint('Identificador unico do dispositivo (UUID)') ?>
        </div>

        <div class="auth-grid-two">
            <?= $form->field($model, 'is_synced')->checkbox()
                ->hint('Marcado se foi sincronizado com o backend') ?>
            <?= $form->field($model, 'is_published')->checkbox()
                ->hint('Marcado se a observacao esta publicada') ?>
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
    const rangeInput = document.getElementById('confidence-range');
    const confidenceValue = document.querySelector('.confidence-value');
    if (rangeInput && confidenceValue) {
        const updateConfidence = () => {
            const value = Math.round(rangeInput.value * 100);
            confidenceValue.textContent = value;
        };
        rangeInput.addEventListener('input', updateConfidence);
        updateConfidence();
    }

    const geoBtn = document.getElementById('get-location-btn');
    if (geoBtn && UIHelpers) {
        geoBtn.addEventListener('click', (e) => {
            e.preventDefault();
            UIHelpers.getGeolocation(
                function(position) {
                    document.querySelector('[name="Observation[latitude]"]').value = position.lat.toFixed(7);
                    document.querySelector('[name="Observation[longitude]"]').value = position.lng.toFixed(7);
                    Notification.success('Localizacao obtida com sucesso!');
                },
                function(error) {
                    Notification.error('Erro ao obter localizacao: ' + error);
                }
            );
        });
    }

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

.form-range {
    height: 8px;
    border-radius: 4px;
    background: var(--gf-surface-soft);
}

.form-range::-webkit-slider-thumb {
    appearance: none;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--gf-brand);
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(62, 122, 87, 0.2);
}

.form-range::-moz-range-thumb {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--gf-brand);
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(62, 122, 87, 0.2);
    border: none;
}

.is-required {
    color: #dc3545;
    margin-left: 2px;
}
</style>
