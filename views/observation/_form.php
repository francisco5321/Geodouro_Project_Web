<?php

use app\models\Observation;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\web\View;

/** @var yii\web\View $this */
/** @var Observation $model */
/** @var array $userOptions */
/** @var array $speciesOptions */

$isNewRecord = $model->isNewRecord;
$isAdminManualReview = !$isNewRecord
    && $model->needsManualReview()
    && (Yii::$app->user->identity?->isAdmin() ?? false);
$hasCoordinates = $model->latitude !== null && $model->longitude !== null;
$showLocationMap = !$isNewRecord && $hasCoordinates;
$submitLabel = $isNewRecord ? 'Criar Observação' : 'Guardar alterações';
$cancelUrl = $isNewRecord ? ['map/index'] : ['observation/view', 'id' => $model->observation_id];
if ($showLocationMap) {
    $this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
    $this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['position' => View::POS_END]);
}
?>
<div class="content-card publication-form-card">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'stacked-form']]); ?>

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
                ->dropDownList($speciesOptions, ['prompt' => 'Sem especie associada']) ?>
        </div>

        <div>
            <?= $form->field($model, 'observed_at')
                ->label($model->getAttributeLabel('observed_at') . ' <span class="is-required">*</span>', ['encode' => false])
                ->input('datetime-local', ['class' => 'form-control']) ?>
            <small class="form-text">Data e hora em que a observação foi feita</small>
        </div>
    </fieldset>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
            Localização Geografica
        </legend>

        <?php if ($showLocationMap): ?>
            <div
                id="observation-location-map"
                class="leaflet-shell observation-location-map"
                data-latitude="<?= Html::encode((string) $model->latitude) ?>"
                data-longitude="<?= Html::encode((string) $model->longitude) ?>"
            ></div>
            <div class="observation-location-summary">
                <strong>Ponto registado:</strong>
                <span><?= Html::encode(number_format((float) $model->latitude, 5) . ', ' . number_format((float) $model->longitude, 5)) ?></span>
            </div>
            <?= Html::activeHiddenInput($model, 'latitude') ?>
            <?= Html::activeHiddenInput($model, 'longitude') ?>
            <?php if ($isAdminManualReview): ?>
                <small class="form-text">Durante a revisao manual, a localizacao original da observacao nao pode ser alterada.</small>
            <?php endif; ?>
        <?php else: ?>
            <div class="auth-grid-two mb-3">
                <?= $form->field($model, 'latitude')
                    ->label('Latitude' . ' <span class="is-required">*</span>', ['encode' => false])
                    ->textInput([
                        'type' => 'number',
                        'step' => '0.0000001',
                        'class' => 'form-control',
                        'readonly' => $isAdminManualReview,
                    ]) ?>
                <?= $form->field($model, 'longitude')
                    ->label('Longitude' . ' <span class="is-required">*</span>', ['encode' => false])
                    ->textInput([
                        'type' => 'number',
                        'step' => '0.0000001',
                        'class' => 'form-control',
                        'readonly' => $isAdminManualReview,
                    ]) ?>
            </div>
            <?php if ($isAdminManualReview): ?>
                <small class="form-text">Durante a revisao manual, a localizacao original da observacao nao pode ser alterada.</small>
            <?php endif; ?>
        <?php endif; ?>

    </fieldset>

    <fieldset class="form-section mb-4">
        <legend class="form-section-title">
            <i class="fas fa-align-left" aria-hidden="true"></i>
            Descrição
        </legend>

        <div>
            <?= $form->field($model, 'notes')
                ->label('Descrição')
                ->textarea([
                    'rows' => 5,
                    'class' => 'form-control observation-description-textarea',
                    'placeholder' => 'Descreva a observação, contexto e detalhes relevantes',
                ])
                ->hint('Descrição e contexto da observação') ?>
        </div>
    </fieldset>

    <div class="form-action-row observation-form-actions">
        <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to($cancelUrl) ?>" title="Cancelar">
            <i class="fas fa-times" aria-hidden="true"></i>
            Cancelar
        </a>
        <?= Html::submitButton(
            '<i class="fas fa-save" aria-hidden="true"></i> ' . $submitLabel,
            ['class' => 'btn btn-brand btn-lg', 'id' => 'submit-btn']
        ) ?>
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

    const mapEl = document.getElementById('observation-location-map');
    if (mapEl && typeof L !== 'undefined') {
        const latitude = Number(mapEl.dataset.latitude);
        const longitude = Number(mapEl.dataset.longitude);

        if (!Number.isNaN(latitude) && !Number.isNaN(longitude)) {
            const map = L.map(mapEl, {
                zoomControl: true,
                scrollWheelZoom: false,
            }).setView([latitude, longitude], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            L.marker([latitude, longitude]).addTo(map)
                .bindPopup('Localizacao exata da observacao')
                .openPopup();

            setTimeout(() => map.invalidateSize(), 0);
        }
    }
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

.observation-location-map {
    height: 360px;
    min-height: 360px;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 0.85rem;
}

.observation-location-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    align-items: center;
    color: var(--gf-text-muted);
    margin-bottom: 0.35rem;
}
</style>
