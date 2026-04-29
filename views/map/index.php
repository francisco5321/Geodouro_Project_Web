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
$this->registerCss(<<<'CSS'
.map-popup-observation {
    min-width: 180px;
}

.map-popup-observation-title {
    display: block;
    margin: 0;
    color: #2f312f;
    font-size: 0.98rem;
    font-weight: 700;
    line-height: 1.25;
}

.map-popup-observation-scientific {
    margin: 0.35rem 0 0;
    color: #4f534f;
    font-size: 0.94rem;
    line-height: 1.35;
}

.map-popup-observation-link {
    display: inline-block;
    margin-top: 0.55rem;
    color: #0b75b8;
    font-size: 0.95rem;
    font-weight: 500;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.map-popup-observation-link:hover,
.map-popup-observation-link:focus {
    color: #085f94;
}

.map-popup-create {
    min-width: 220px;
}

.map-popup-create-title {
    display: block;
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1e2b24;
    letter-spacing: -0.01em;
}

.map-popup-create-coordinates {
    margin: 0.45rem 0 0;
    font-size: 0.86rem;
    line-height: 1.45;
    color: #66756d;
    font-variant-numeric: tabular-nums;
}

.map-popup-create-link {
    width: 100%;
    min-height: 42px;
    padding: 0.7rem 1rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #20593f 0%, #2e7a57 100%);
    box-shadow: 0 10px 22px rgba(32, 89, 63, 0.18);
    color: #fff;
    font-size: 0.92rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    -webkit-text-fill-color: #fff;
    transition: transform 0.16s ease, box-shadow 0.16s ease, background 0.16s ease;
}

.map-popup-create-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(32, 89, 63, 0.24);
    background: linear-gradient(135deg, #1b4d37 0%, #276a4c 100%);
    color: #fff;
    -webkit-text-fill-color: #fff;
}

.map-popup-create-link:visited,
.map-popup-create-link:focus,
.map-popup-create-link:active {
    color: #fff;
    -webkit-text-fill-color: #fff;
}
CSS);
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
        <div class="map-popup map-popup-observation">
            <strong class="map-popup-observation-title">${marker.title}</strong>
            <p class="map-popup-observation-scientific">${marker.scientificName}</p>
            <a class="map-popup-observation-link" href="${marker.detailUrl}">Abrir observação</a>
        </div>
    `;
    const layer = L.marker(point).addTo(map).bindPopup(popup);

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
                <div class="map-popup map-popup-rich map-popup-create">
                    <strong class="map-popup-create-title">Novo ponto de observação</strong>
                    <p class="map-popup-create-coordinates">${lat}, ${lng}</p>
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
            <p class="hero-text">Explora todas as observações botânicas num mapa interativo e usa os percursos para desenhar o teu futuro roteiro de campo.</p>
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
