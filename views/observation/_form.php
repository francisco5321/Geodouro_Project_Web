<?php

use app\components\FileUploadField;
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

    <!-- Seção: Informações Básicas -->
    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            Informações Básicas
        </legend>

        <div class="auth-grid-two">
            <?= $form->field($model, 'user_id')
                ->label($model->getAttributeLabel('user_id') . ' <span class="is-required">*</span>', ['encode' => false])
                ->dropDownList($userOptions, ['prompt' => 'Seleciona o autor']) ?>
            <?= $form->field($model, 'plant_species_id')
                ->label($model->getAttributeLabel('plant_species_id'), ['class' => 'form-label'])
                ->dropDownList($speciesOptions, ['prompt' => 'Sem espécie associada']) ?>
        </div>

        <div>
            <?= $form->field($model, 'observed_at')
                ->label($model->getAttributeLabel('observed_at') . ' <span class="is-required">*</span>', ['encode' => false])
                ->input('datetime-local', ['class' => 'form-control']) ?>
            <small class="form-text">Data e hora em que a observação foi feita</small>
        </div>
    </fieldset>

    <!-- Seção: Localização -->
    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
            Localização Geográfica
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
            Obter localização atual
        </button>
        <small class="form-text d-block mt-2">Use GPS do seu dispositivo para preencher as coordenadas</small>
    </fieldset>

    <!-- Seção: Dados da Observação -->
    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-microscope" aria-hidden="true"></i>
            Detalhes da Observação
        </legend>

        <div class="auth-grid-two">
            <?= $form->field($model, 'confidence')
                ->label('Confiança (' . '<span class="confidence-value">0</span>%)', ['encode' => false])
                ->textInput([
                    'type' => 'range',
                    'step' => '0.01',
                    'min' => 0,
                    'max' => 1,
                    'class' => 'form-range',
                    'id' => 'confidence-range'
                ])->hint('Nível de confiança da identificação de 0 a 100%') ?>
            <?= $form->field($model, 'captured_at')
                ->label('Timestamp do dispositivo')
                ->textInput(['type' => 'number', 'step' => '1', 'class' => 'form-control'])
                ->hint('Segundos desde epoch (1970-01-01)') ?>
        </div>

        <div>
            <?= $form->field($model, 'notes')
                ->label('Notas de campo')
                ->textarea(['rows' => 5, 'class' => 'form-control', 'placeholder' => 'Descreva a observação, contexto e detalhes relevantes', 'data-auto-resize' => true])
                ->hint('Informações adicionais e contexto da observação') ?>
        </div>
    </fieldset>

    <!-- Seção: Classificação -->
    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-tag" aria-hidden="true"></i>
            Classificação da Espécie
        </legend>

        <div class="auth-grid-two">
            <?= $form->field($model, 'predicted_scientific_name')
                ->label('Nome científico predito')
                ->textInput(['class' => 'form-control'])
                ->hint('Resultado da classificação automática') ?>
            <?= $form->field($model, 'enriched_scientific_name')
                ->label('Nome científico enriquecido')
                ->textInput(['class' => 'form-control'])
                ->hint('Dados validados do backend') ?>
        </div>

        <div class="auth-grid-two">
            <?= $form->field($model, 'enriched_common_name')
                ->label('Nome comum')
                ->textInput(['class' => 'form-control'])
                ->hint('Nome vernacular da espécie') ?>
            <?= $form->field($model, 'enriched_family')
                ->label('Família')
                ->textInput(['class' => 'form-control'])
                ->hint('Família botânica') ?>
        </div>
    </fieldset>

    <!-- Seção: Imagem e Referências -->
    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-image" aria-hidden="true"></i>
            Imagem e Referências
        </legend>

        <div>
            <?= $form->field($model, 'image_uri')
                ->label('Imagem principal')
                ->textInput(['class' => 'form-control', 'placeholder' => 'Caminho ou URL da imagem'])
                ->hint('URL ou caminho relativo para a imagem principal da observação') ?>
        </div>

        <div class="auth-grid-two">
            <?= $form->field($model, 'enriched_wikipedia_url')
                ->label('Referência Wikipedia')
                ->textInput(['class' => 'form-control', 'type' => 'url', 'placeholder' => 'https://pt.wikipedia.org/wiki/...'])
                ->hint('Link para Wikipedia sobre a espécie') ?>
            <?= $form->field($model, 'enriched_photo_url')
                ->label('Referência de foto')
                ->textInput(['class' => 'form-control', 'type' => 'url', 'placeholder' => 'https://...'])
                ->hint('Link para foto de referência') ?>
        </div>
    </fieldset>

    <!-- Seção: Status de Sincronização -->
    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-sync-alt" aria-hidden="true"></i>
            Status de Sincronização
        </legend>

        <div class="auth-grid-two">
            <?= $form->field($model, 'sync_status')
                ->label('Status de sincronização')
                ->dropDownList([
                    Observation::SYNC_PENDING => 'Pendente',
                    Observation::SYNC_SYNCED => 'Sincronizada',
                    Observation::SYNC_FAILED => 'Falhada',
                ])
                ->hint('Estado de sincronização com backend') ?>
            <?= $form->field($model, 'device_observation_id')
                ->label('ID do dispositivo')
                ->textInput(['class' => 'form-control', 'placeholder' => 'UUID opcional'])
                ->hint('Identificador único do dispositivo (UUID)') ?>
        </div>

        <div class="auth-grid-two">
            <?= $form->field($model, 'is_synced')->checkbox()
                ->hint('Marcado se foi sincronizado com o backend') ?>
            <?= $form->field($model, 'is_published')->checkbox()
                ->hint('Marcado se a observação está publicada') ?>
        </div>
    </fieldset>

    <!-- Botões de Ação -->
    <div class="form-action-row">
        <?= Html::submitButton(
            '<i class="fas fa-save" aria-hidden="true"></i> Criar observação',
            ['class' => 'btn btn-brand btn-lg', 'id' => 'submit-btn']
        ) ?>
        <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to(['map/index']) ?>" title="Cancelar criação">
            <i class="fas fa-times" aria-hidden="true"></i>
            Cancelar
        </a>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
// JavaScript para melhorias de UX
$js = <<<'JS'
document.addEventListener('DOMContentLoaded', function() {
    // Atualizar display de confiança em tempo real
    const rangeInput = document.getElementById('confidence-range');
    const confidenceValue = document.querySelector('.confidence-value');
    if (rangeInput && confidenceValue) {
        const updateConfidence = () => {
            const value = Math.round(rangeInput.value * 100);
            confidenceValue.textContent = value;
        };
        rangeInput.addEventListener('input', updateConfidence);
        updateConfidence(); // Inicializar
    }

    // Botão de geolocalização
    const geoBtn = document.getElementById('get-location-btn');
    if (geoBtn && UIHelpers) {
        geoBtn.addEventListener('click', (e) => {
            e.preventDefault();
            UIHelpers.getGeolocation(
                function(position) {
                    document.querySelector('[name="Observation[latitude]"]').value = position.lat.toFixed(7);
                    document.querySelector('[name="Observation[longitude]"]').value = position.lng.toFixed(7);
                    Notification.success('Localização obtida com sucesso!');
                },
                function(error) {
                    Notification.error('Erro ao obter localização: ' + error);
                }
            );
        });
    }

    // Adicionar classe de carregamento ao enviar
    const form = document.querySelector('.stacked-form');
    const submitBtn = document.getElementById('submit-btn');
    if (form && submitBtn) {
        form.addEventListener('submit', (e) => {
            // Validação básica (Bootstrap faz o resto)
            if (form.querySelectorAll('.is-invalid').length === 0) {
                UIHelpers.setButtonLoading(submitBtn);
            }
        });
    }

    // Auto-redimensionar textarea
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
