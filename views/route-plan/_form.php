<?php

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\Json;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\models\RoutePlan $model */

$this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
$this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['position' => View::POS_END]);
$startPointJson = Json::htmlEncode($model->getStartPoint());
$js = <<<'JS'
const initialStartPoint = __START_POINT__;
const startMapElement = document.getElementById('route-start-map');
const latitudeInput = document.getElementById('routeplan-start_latitude');
const longitudeInput = document.getElementById('routeplan-start_longitude');
const labelInput = document.getElementById('routeplan-start_label');
const clearButton = document.getElementById('clear-route-start-point');
const statusElement = document.getElementById('route-start-status');

if (startMapElement && latitudeInput && longitudeInput) {
    const startMap = L.map(startMapElement).setView([41.3, -7.7], 8);
    let startMarker = null;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(startMap);

    function updateStatus(message) {
        if (statusElement) {
            statusElement.textContent = message;
        }
    }

    function setStartPoint(lat, lng, label = null) {
        latitudeInput.value = Number(lat).toFixed(7);
        longitudeInput.value = Number(lng).toFixed(7);
        if (label !== null && labelInput && !labelInput.value.trim()) {
            labelInput.value = label;
        }

        if (startMarker) {
            startMap.removeLayer(startMarker);
        }

        startMarker = L.marker([lat, lng]).addTo(startMap).bindPopup(
            `<strong>${labelInput?.value?.trim() || 'Ponto de partida'}</strong><p>${Number(lat).toFixed(5)}, ${Number(lng).toFixed(5)}</p>`
        );
        startMarker.openPopup();
        startMap.setView([lat, lng], Math.max(startMap.getZoom(), 13));
        updateStatus('Ponto de partida definido. O percurso vai comecar e terminar aqui.');
    }

    startMap.on('click', (event) => {
        setStartPoint(event.latlng.lat, event.latlng.lng);
    });

    if (clearButton) {
        clearButton.addEventListener('click', () => {
            latitudeInput.value = '';
            longitudeInput.value = '';
            if (labelInput) {
                labelInput.value = '';
            }
            if (startMarker) {
                startMap.removeLayer(startMarker);
                startMarker = null;
            }
            startMap.setView([41.3, -7.7], 8);
            updateStatus('Sem ponto de partida personalizado. O percurso comeca na primeira paragem.');
        });
    }

    if (labelInput) {
        labelInput.addEventListener('input', () => {
            if (startMarker) {
                startMarker.bindPopup(`<strong>${labelInput.value.trim() || 'Ponto de partida'}</strong><p>${latitudeInput.value}, ${longitudeInput.value}</p>`);
            }
        });
    }

    if (initialStartPoint && initialStartPoint.latitude && initialStartPoint.longitude) {
        setStartPoint(initialStartPoint.latitude, initialStartPoint.longitude, initialStartPoint.label || null);
    } else {
        updateStatus('Clica no mapa para definires um ponto de partida opcional.');
    }
}
JS;
$js = str_replace('__START_POINT__', $startPointJson ?: 'null', $js);
$this->registerJs($js, View::POS_END);
?>
<div class="content-card publication-form-card">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'stacked-form']]); ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Ex.: Primavera no Douro']) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 5, 'placeholder' => 'Define o objetivo deste percurso e as plantas/publicacoes que queres priorizar.']) ?>

        <section class="detail-section route-start-section">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Partida</span>
                    <h2>Escolher ponto de partida</h2>
                </div>
            </div>
            <p id="route-start-status" class="route-start-status">Clica no mapa para definires um ponto de partida opcional.</p>
            <div id="route-start-map" class="route-start-map"></div>
            <div class="visit-route-builder-grid mt-3">
                <div>
                    <?= $form->field($model, 'start_label')->textInput(['maxlength' => true, 'placeholder' => 'Ex.: Parque de estacionamento']) ?>
                </div>
                <div class="route-start-coordinates">
                    <?= $form->field($model, 'start_latitude')->textInput(['readonly' => true, 'placeholder' => 'Latitude escolhida no mapa']) ?>
                    <?= $form->field($model, 'start_longitude')->textInput(['readonly' => true, 'placeholder' => 'Longitude escolhida no mapa']) ?>
                </div>
            </div>
            <div class="form-action-row route-start-actions">
                <button type="button" id="clear-route-start-point" class="btn btn-outline-brand">Limpar ponto de partida</button>
            </div>
        </section>

        <div class="form-action-row">
            <?= Html::submitButton($model->isNewRecord ? 'Criar percurso' : 'Guardar percurso', ['class' => 'btn btn-brand btn-lg']) ?>
            <a class="btn btn-outline-brand btn-lg" href="<?= yii\helpers\Url::to($model->isNewRecord ? ['route-plan/index'] : ['route-plan/view', 'id' => $model->route_plan_id]) ?>">Cancelar</a>
        </div>
    <?php ActiveForm::end(); ?>
</div>
