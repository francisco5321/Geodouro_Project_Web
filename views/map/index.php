<?php

use app\models\Observation;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var Observation[] $observations */
/** @var string $markersJson */
/** @var int $visitTargetCount */
/** @var bool $canCreateObservation */
/** @var string|null $createObservationUrl */

$this->title = 'Mapa';
$this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
$this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['position' => View::POS_END]);
$js = <<<'JS'
const markers = __MARKERS__ || [];
const canCreateObservation = __CAN_CREATE__;
const createObservationBaseUrl = '__CREATE_OBSERVATION_URL__';
const map = L.map('geodouro-map').setView([41.3, -7.7], 8);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);
const bounds = [];
markers.forEach((marker) => {
    const point = [marker.latitude, marker.longitude];
    bounds.push(point);
    const popup = `
        <div class="map-popup">
            <strong>${marker.title}</strong>
            <p>${marker.scientificName}</p>
            <p>${marker.author} - ${marker.status}</p>
            <a href="${marker.detailUrl}">Abrir observacao</a>
        </div>
    `;
    L.circleMarker(point, {
        radius: marker.isVisitTarget ? 10 : 6,
        color: marker.isVisitTarget ? '#1f5f43' : '#6d7f72',
        fillColor: marker.isVisitTarget ? '#7bc47f' : '#c8d2c6',
        fillOpacity: marker.isVisitTarget ? 0.95 : 0.55,
        weight: marker.isVisitTarget ? 2.5 : 1.5,
    }).addTo(map).bindPopup(popup);
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
                    <strong>Novo ponto de observacao</strong>
                    <p>${lat}, ${lng}</p>
                    <div class="map-popup-actions">
                        <a class="map-popup-create-link" href="${createUrl}">Criar observacao aqui</a>
                    </div>
                </div>
            `)
            .openOn(map);
    });
}
if (bounds.length > 0) {
    map.fitBounds(bounds, {padding: [32, 32]});
}
JS;
$js = str_replace('__MARKERS__', $markersJson, $js);
$js = str_replace('__CAN_CREATE__', $canCreateObservation ? 'true' : 'false', $js);
$js = str_replace('__CREATE_OBSERVATION_URL__', $createObservationUrl ?? '', $js);
$this->registerJs($js, View::POS_END);
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Mapa de observacoes</span>
            <h1 class="hero-title hero-title-tight">Observações</h1>
            <p class="hero-text">Os pontos assinalados em Quero visitar aparecem destacados para já ires desenhando o teu futuro roteiro de campo.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Observações</span><strong><?= count($observations) ?></strong></article>
            <article class="detail-stat-card"><span>Quero visitar</span><strong><?= (int) $visitTargetCount ?></strong></article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <section class="toolbar-card mb-4">
        <div class="toolbar-row">
            <div>
                <strong><?= $canCreateObservation ? 'Criacao manual ativa' : 'Mapa operacional' ?></strong>
                <p class="table-subtext mb-0"><?= $canCreateObservation ? 'Sendo admin, podes clicar em qualquer ponto do mapa para criar uma observacao com coordenadas pre-preenchidas.' : 'Explora o territorio e abre o detalhe das observacoes registadas.' ?></p>
            </div>
            <?php if ($canCreateObservation): ?>
                <div class="toolbar-actions">
                    <a class="btn btn-brand" href="<?= Url::to(['observation/create']) ?>">Nova observacao manual</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="map-layout">
        <div id="geodouro-map" class="leaflet-shell"></div>
        <aside class="map-sidebar">
            <h2>Ultimas observacoes com coordenadas</h2>
            <div class="map-observation-list">
                <?php foreach (array_slice($observations, 0, 8) as $observation): ?>
                    <article class="map-observation-item">
                        <p class="species-scientific-name"><?= Html::encode($observation->getResolvedScientificName() ?: 'Sem classificacao') ?></p>
                        <h3><?= Html::encode($observation->getResolvedCommonName() ?: 'Observacao botanica') ?></h3>
                        <p><?= Html::encode($observation->user?->getFullName() ?? 'Sistema') ?></p>
                        <a href="<?= Url::to(['observation/view', 'id' => $observation->observation_id]) ?>">Abrir detalhe</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </aside>
    </section>
</div>
