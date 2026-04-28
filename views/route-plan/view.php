<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var array $plan */
/** @var array $stops */
/** @var app\services\ApiPlantSpecies[] $plannableSpecies */
/** @var string $speciesSearch */
/** @var string $markersJson */
/** @var string $backgroundMarkersJson */
/** @var string $routeCoordinatesJson */

$planId = (int) ($plan['routePlanId'] ?? 0);
$planName = (string) ($plan['name'] ?? 'Percurso');
$planDescription = $plan['description'] ?? null;

$this->title = $planName;
$this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
$this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['position' => View::POS_END]);
$toggleUrl = Url::to(['route-plan/toggle-observation-point', 'id' => $planId]);

$js = <<<'JS'
const routeMarkers = __ROUTE_MARKERS__ || [];
const backgroundMarkers = __BACKGROUND_MARKERS__ || [];
const routeCoordinates = __ROUTE_COORDS__ || [];
const toggleObservationUrl = '__TOGGLE_URL__';
const routeMap = L.map('route-plan-map').setView([41.3, -7.7], 8);
let routePathLayer = null;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const csrfParam = document.querySelector('meta[name="csrf-param"]')?.content || '_csrf';
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(routeMap);

const bounds = [];
function popup(marker) {
    const label = marker.isInRoute ? 'Remover do percurso' : 'Adicionar ao percurso';
    return `<div class="map-popup map-popup-rich">
        <strong>${marker.title}</strong>
        <p>${marker.scientificName || marker.subtitle || ''}</p>
        <div class="map-popup-actions">
            ${marker.detailUrl ? `<a href="${marker.detailUrl}">Abrir observação</a>` : ''}
            ${marker.id ? `<button type="button" class="map-popup-button js-route-observation-toggle" data-observation-id="${marker.id}">${label}</button>` : ''}
        </div>
    </div>`;
}

backgroundMarkers.forEach((marker) => {
    if (marker.latitude == null || marker.longitude == null) return;
    const point = [marker.latitude, marker.longitude];
    bounds.push(point);
    L.circleMarker(point, {
        radius: marker.isInRoute ? 9 : 5,
        color: marker.isInRoute ? '#1f5f43' : '#8ca194',
        fillColor: marker.isInRoute ? '#7bc47f' : '#d7e0d5',
        fillOpacity: marker.isInRoute ? 0.9 : 0.35,
        weight: marker.isInRoute ? 2 : 1,
    }).addTo(routeMap).bindPopup(popup(marker));
});

routeMarkers.forEach((marker) => {
    const point = [marker.latitude, marker.longitude];
    bounds.push(point);
    L.circleMarker(point, {
        radius: 10,
        color: '#1f5f43',
        fillColor: '#7bc47f',
        fillOpacity: 0.95,
        weight: 2.5,
    }).addTo(routeMap).bindPopup(`<strong>${marker.order}. ${marker.title}</strong><p>${marker.subtitle || ''}</p>`);
});

function drawFallbackRoute() {
    if (routeCoordinates.length <= 1) return;
    const fallbackCoordinates = routeCoordinates.length > 2
        ? [...routeCoordinates, routeCoordinates[0]]
        : routeCoordinates;

    routePathLayer = L.polyline(fallbackCoordinates, {
        color: '#1f5f43',
        weight: 5,
        opacity: 0.85,
    }).addTo(routeMap);
}

if (bounds.length > 0) {
    routeMap.fitBounds(bounds, {padding: [28, 28]});
}

async function drawRoutedPath() {
    if (routeCoordinates.length <= 1) return;

    const waypointList = routeCoordinates.map(([latitude, longitude]) => `${longitude},${latitude}`).join(';');

    try {
        const response = await fetch(`https://router.project-osrm.org/route/v1/foot/${waypointList}?overview=full&geometries=geojson`);
        if (!response.ok) throw new Error(`Routing HTTP ${response.status}`);
        const data = await response.json();
        const coordinates = data?.routes?.[0]?.geometry?.coordinates;
        if (!Array.isArray(coordinates) || coordinates.length < 2) {
            throw new Error('Routing geometry missing');
        }

        const latLngs = coordinates
            .filter((coordinate) => Array.isArray(coordinate) && coordinate.length >= 2)
            .map(([longitude, latitude]) => [latitude, longitude]);

        if (latLngs.length < 2) {
            throw new Error('Routing geometry invalid');
        }

        routePathLayer = L.polyline(latLngs, {
            color: '#1f5f43',
            weight: 5,
            opacity: 0.85,
        }).addTo(routeMap);
    } catch (error) {
        console.warn('Failed to draw routed path, falling back to straight polyline.', error);
        drawFallbackRoute();
    }
}

drawRoutedPath();

document.addEventListener('click', async (event) => {
    const button = event.target.closest('.js-route-observation-toggle');
    if (!button) return;
    event.preventDefault();
    button.disabled = true;
    const body = new URLSearchParams();
    body.append(csrfParam, csrfToken);
    const response = await fetch(`${toggleObservationUrl}&observationId=${button.dataset.observationId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: body.toString(),
    });
    if (response.ok) window.location.reload();
});
JS;
$js = str_replace('__ROUTE_MARKERS__', $markersJson, $js);
$js = str_replace('__BACKGROUND_MARKERS__', $backgroundMarkersJson, $js);
$js = str_replace('__ROUTE_COORDS__', $routeCoordinatesJson, $js);
$js = str_replace('__TOGGLE_URL__', $toggleUrl, $js);
$this->registerJs($js, View::POS_END);
?>
<div class="module-shell">
    <a class="back-link" href="<?= Url::to(['route-plan/index']) ?>">&larr; Voltar aos percursos</a>

    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Percurso planeado</span>
            <h1 class="hero-title hero-title-tight"><?= Html::encode($planName) ?></h1>
            <p class="hero-text"><?= Html::encode($planDescription ?: 'Sem descrição definida para este percurso.') ?></p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Paragens</span><strong><?= count($stops) ?></strong></article>
            <article class="detail-stat-card"><span>Trajeto</span><strong>Circuito</strong></article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-geoflora mb-4"><?= Yii::$app->session->getFlash('error') ?></div>
    <?php endif; ?>

    <section class="toolbar-card mb-4">
        <div class="toolbar-row">
            <div>
                <strong>Gestão do percurso</strong>
                <p class="table-subtext mb-0">Seleciona observações no mapa ou adiciona espécies pela lista abaixo.</p>
            </div>
            <div class="toolbar-actions">
                <a class="btn btn-outline-brand" href="<?= Url::to(['route-plan/update', 'id' => $planId]) ?>">Editar percurso</a>
                <?= Html::beginForm(['route-plan/delete', 'id' => $planId], 'post') ?>
                    <?= Html::submitButton('Eliminar percurso', ['class' => 'btn btn-outline-danger', 'data-confirm' => 'Queres mesmo eliminar este percurso?']) ?>
                <?= Html::endForm() ?>
            </div>
        </div>
    </section>

    <section class="map-layout mb-4">
        <div id="route-plan-map" class="leaflet-shell"></div>
        <aside class="map-sidebar">
            <h2>Ordem de visita</h2>
            <div class="map-observation-list">
                <?php if (empty($stops)): ?>
                    <div class="empty-state-card compact-empty-state">
                        <h3>Sem paragens ainda</h3>
                        <p>Clica num ponto do mapa para adicionar a primeira paragem.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($stops as $point): ?>
                        <article class="map-observation-item target-item-card">
                            <p class="species-scientific-name">Paragem <?= (int) ($point['visitOrder'] ?? 0) ?></p>
                            <h3><?= Html::encode($point['title'] ?? 'Alvo') ?></h3>
                            <p><?= Html::encode($point['subtitle'] ?? 'Sem subtitulo') ?></p>
                            <div class="timeline-card-actions">
                                <?php if (($point['observationId'] ?? null) !== null): ?>
                                    <a href="<?= Url::to(['observation/view', 'id' => $point['observationId']]) ?>">Abrir observação</a>
                                    <button type="button" class="link-button js-route-observation-toggle" data-observation-id="<?= (int) $point['observationId'] ?>">Remover do percurso</button>
                                <?php elseif (($point['publicationId'] ?? null) !== null): ?>
                                    <a href="<?= Url::to(['publication/view', 'id' => $point['publicationId']]) ?>">Abrir publicação</a>
                                <?php elseif (($point['plantSpeciesId'] ?? null) !== null): ?>
                                    <a href="<?= Url::to(['species/view', 'id' => $point['plantSpeciesId']]) ?>">Abrir espécie</a>
                                <?php endif; ?>
                                <?php if (($point['observationId'] ?? null) === null): ?>
                                    <?= Html::beginForm(['route-plan/remove-point', 'id' => $point['routePlanPointId']], 'post') ?>
                                        <?= Html::hiddenInput('routePlanId', $planId) ?>
                                        <?= Html::submitButton('Remover', ['class' => 'link-button']) ?>
                                    <?= Html::endForm() ?>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>
    </section>

    <section class="detail-section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Alternativas</span>
                <h2>Adicionar plantas ao percurso</h2>
            </div>
        </div>
        <section class="toolbar-card mb-4">
            <?= Html::beginForm(['route-plan/view', 'id' => $planId], 'get', ['class' => 'user-search-form']) ?>
                <div class="user-search-input-wrap">
                    <?= Html::textInput('speciesQ', $speciesSearch, ['class' => 'form-control user-search-input', 'placeholder' => 'Pesquisar plantas']) ?>
                </div>
                <div class="toolbar-actions">
                    <?= Html::submitButton('Pesquisar plantas', ['class' => 'btn btn-brand']) ?>
                    <?php if ($speciesSearch !== ''): ?>
                        <a class="btn btn-outline-brand" href="<?= Url::to(['route-plan/view', 'id' => $planId]) ?>">Limpar</a>
                    <?php endif; ?>
                </div>
            <?= Html::endForm() ?>
        </section>
        <?php if (empty($plannableSpecies)): ?>
            <div class="empty-state-card">
                <h3>Sem plantas disponiveis</h3>
                <p>Não encontramos plantas para esta pesquisa.</p>
            </div>
        <?php else: ?>
            <div class="observation-list">
                <?php foreach ($plannableSpecies as $species): ?>
                    <article class="observation-card-web">
                        <div class="observation-card-top">
                            <div>
                                <p class="observation-title"><?= Html::encode($species->getDisplayName()) ?></p>
                                <p class="observation-subtitle"><?= Html::encode($species->scientific_name) ?></p>
                            </div>
                            <span class="status-pill"><?= Html::encode($species->family) ?></span>
                        </div>
                        <div class="timeline-card-actions mt-3">
                            <?= Html::beginForm(['route-plan/add-species', 'id' => $planId, 'speciesId' => $species->plant_species_id], 'post') ?>
                                <?= Html::hiddenInput('speciesQ', $speciesSearch) ?>
                                <?= Html::submitButton('Adicionar planta ao percurso', ['class' => 'btn btn-brand']) ?>
                            <?= Html::endForm() ?>
                            <a href="<?= Url::to(['species/view', 'id' => $species->plant_species_id]) ?>">Abrir espécie</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
