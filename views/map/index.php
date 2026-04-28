<?php

use app\components\StatCard;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\services\ApiObservation[] $observations */
/** @var string $markersJson */
/** @var int $visitTargetCount */
/** @var bool $canCreateObservation */
/** @var string|null $createObservationUrl */
/** @var int $focusObservationId */

$this->title = 'Mapa';
$this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
$this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['position' => View::POS_END]);
$js = <<<'JS'
const markers = __MARKERS__ || [];
const canCreateObservation = __CAN_CREATE__;
const createObservationBaseUrl = '__CREATE_OBSERVATION_URL__';
const focusObservationId = __FOCUS_OBSERVATION_ID__;
const map = L.map('geodouro-map').setView([41.3, -7.7], 8);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);
const bounds = [];
let focusedLayer = null;
markers.forEach((marker) => {
    const point = [marker.latitude, marker.longitude];
    bounds.push(point);
    const popup = `
        <div class="map-popup">
            <strong>${marker.title}</strong>
            <p>${marker.scientificName}</p>
            <p>${marker.status}</p>
            <a href="${marker.detailUrl}">Abrir observação</a>
        </div>
    `;
    const layer = L.circleMarker(point, {
        radius: marker.isVisitTarget ? 10 : 6,
        color: marker.isVisitTarget ? '#1f5f43' : '#6d7f72',
        fillColor: marker.isVisitTarget ? '#7bc47f' : '#c8d2c6',
        fillOpacity: marker.isVisitTarget ? 0.95 : 0.55,
        weight: marker.isVisitTarget ? 2.5 : 1.5,
    }).addTo(map).bindPopup(popup);

    if (Number(marker.id) === Number(focusObservationId)) {
        focusedLayer = layer;
    }
});
if (canCreateObservation) {
    map.on('click', (event) => {
        const lat = event.latlng.lat.toFixed(7);
        const lng = event.latlng.lng.toFixed(7);
        const createUrl = `${createObservationBaseUrl}?latitude=${lat}&longitude=${lng}`;
        L.popup()
            .setLatLng(event.latlng)
            .setContent(`
                <div class="map-popup map-popup-rich">
                    <strong>Novo ponto de observação</strong>
                    <p>${lat}, ${lng}</p>
                    <div class="map-popup-actions">
                        <a class="map-popup-create-link" href="${createUrl}">Criar observação aqui</a>
                    </div>
                </div>
            `)
            .openOn(map);
    });
}
if (focusedLayer) {
    const focusedPoint = focusedLayer.getLatLng();
    map.setView(focusedPoint, 16, {animate: false});
    focusedLayer.openPopup();
} else if (bounds.length > 0) {
    map.fitBounds(bounds, {padding: [32, 32]});
}
JS;
$js = str_replace('__MARKERS__', $markersJson, $js);
$js = str_replace('__CAN_CREATE__', $canCreateObservation ? 'true' : 'false', $js);
$js = str_replace('__CREATE_OBSERVATION_URL__', $createObservationUrl ?? '', $js);
$js = str_replace('__FOCUS_OBSERVATION_ID__', (string) (int) $focusObservationId, $js);
$this->registerJs($js, View::POS_END);
?>
<div class="module-shell">
    <section class="species-hero map-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-map" aria-hidden="true"></i>
                Mapa Interativo
            </span>
            <h1 class="hero-title hero-title-tight">Observações no Território</h1>
            <p class="hero-text">Explora todas as observacoes botanicas num mapa interativo e usa os percursos para desenhar o teu futuro roteiro de campo.</p>
        </div>
        <div class="detail-stat-grid">
            <?= StatCard::widget([
                'label' => 'Observações',
                'value' => (int) count($observations),
                'icon' => 'fas fa-binoculars',
            ]) ?>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert-success-custom mb-4">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <section class="catalog-toolbar mb-4">
        <div class="toolbar-header">
            <h2 class="section-title">
                <i class="fas fa-info-circle" aria-hidden="true"></i>
                <?= $canCreateObservation ? 'Modo Criação Ativo' : 'Modo Consulta' ?>
            </h2>
            <p class="section-description mb-0">
                <?= $canCreateObservation ? 'Sendo administrador, podes clicar em qualquer ponto do mapa para criar uma observação com coordenadas pré-preenchidas.' : 'Explora o território e abre o detalhe das observações registadas.' ?>
            </p>
        </div>
        <?php if ($canCreateObservation): ?>
            <div class="toolbar-actions">
                <a class="btn btn-brand" href="<?= Url::to(['observation/create']) ?>">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    Nova Observação Manual
                </a>
            </div>
        <?php endif; ?>
    </section>

    <section class="map-layout">
        <div id="geodouro-map" class="leaflet-shell"></div>
        <aside class="map-sidebar">
            <h2 class="sidebar-title">
                <i class="fas fa-list" aria-hidden="true"></i>
                Últimas Observações
            </h2>
            <div class="map-observation-list">
                <?php foreach (array_slice($observations, 0, 8) as $observation): ?>
                    <article class="map-observation-item">
                        <p class="species-scientific-name"><?= Html::encode($observation->getResolvedScientificName() ?: 'Sem classificação') ?></p>
                        <h3><?= Html::encode($observation->getResolvedCommonName() ?: 'Observação botânica') ?></h3>
                        <a href="<?= Url::to(['observation/view', 'id' => $observation->observation_id]) ?>">Abrir detalhe</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </aside>
    </section>
</div>
