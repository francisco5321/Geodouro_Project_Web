<?php

use app\models\Observation;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var Observation[] $observations */
/** @var string $markersJson */

$this->title = 'Mapa';
$this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
$this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['position' => View::POS_END]);
$js = <<<'JS'
const markers = __MARKERS__ || [];
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
            <p>${marker.author} · ${marker.status}</p>
            <a href="${marker.detailUrl}">Abrir observacao</a>
        </div>
    `;
    L.marker(point).addTo(map).bindPopup(popup);
});
if (bounds.length > 0) {
    map.fitBounds(bounds, {padding: [32, 32]});
}
JS;
$js = str_replace('__MARKERS__', $markersJson, $js);
$this->registerJs($js, View::POS_END);
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Mapa de observacoes</span>
            <h1 class="hero-title hero-title-tight">Leaflet como camada de leitura territorial do projeto</h1>
            <p class="hero-text">Este modulo cruza os registos georreferenciados da base de dados com um mapa navegavel, abrindo caminho a filtros e clusters mais tarde.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Marcadores</span><strong><?= count($observations) ?></strong></article>
            <article class="detail-stat-card"><span>Motor</span><strong>Leaflet</strong></article>
            <article class="detail-stat-card"><span>Camada</span><strong>OSM</strong></article>
            <article class="detail-stat-card"><span>Estado</span><strong>Ativo</strong></article>
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
