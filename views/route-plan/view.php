<?php

use app\models\PlantSpecies;
use app\models\RoutePlan;
use app\models\SavedVisitTarget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var RoutePlan $plan */
/** @var SavedVisitTarget[] $availableTargets */
/** @var PlantSpecies[] $plannableSpecies */
/** @var string $speciesSearch */
/** @var string $markersJson */
/** @var string $backgroundMarkersJson */
/** @var string $routeCoordinatesJson */

$this->title = $plan->name;
$this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
$this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['position' => View::POS_END]);
$toggleUrl = Url::to(['route-plan/toggle-observation-point', 'id' => $plan->route_plan_id]);
$js = <<<'JS'
const routeMarkers = __ROUTE_MARKERS__ || [];
const backgroundMarkers = __BACKGROUND_MARKERS__ || [];
const routeCoordinates = __ROUTE_COORDS__ || [];
const toggleObservationUrl = '__TOGGLE_URL__';
const routeMap = L.map('route-plan-map').setView([41.3, -7.7], 8);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const csrfParam = document.querySelector('meta[name="csrf-param"]')?.content || '_csrf';
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(routeMap);
const bounds = [];
function buildObservationPopup(marker) {
    const buttonLabel = marker.isInRoute ? 'Remover do percurso' : 'Quero passar aqui';
    return `
        <div class="map-popup map-popup-rich">
            <strong>${marker.title}</strong>
            <p>${marker.scientificName}</p>
            <p>${marker.status}</p>
            <div class="map-popup-actions">
                <a href="${marker.detailUrl}">Abrir observacao</a>
                <button type="button" class="map-popup-button" data-observation-id="${marker.id}">${buttonLabel}</button>
            </div>
        </div>
    `;
}
backgroundMarkers.forEach((marker) => {
    const point = [marker.latitude, marker.longitude];
    bounds.push(point);
    const circle = L.circleMarker(point, {
        radius: marker.isInRoute ? 9 : 5,
        color: marker.isInRoute ? '#1f5f43' : '#8ca194',
        fillColor: marker.isInRoute ? '#7bc47f' : '#d7e0d5',
        fillOpacity: marker.isInRoute ? 0.9 : 0.35,
        weight: marker.isInRoute ? 2 : 1,
    }).addTo(routeMap).bindPopup(buildObservationPopup(marker));
    circle.on('popupopen', () => {
        const button = document.querySelector(`.map-popup-button[data-observation-id="${marker.id}"]`);
        if (!button) {
            return;
        }
        button.addEventListener('click', async () => {
            const body = new URLSearchParams();
            body.append(csrfParam, csrfToken);
            const response = await fetch(`${toggleObservationUrl}&observationId=${marker.id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                },
                body: body.toString(),
            });
            if (response.ok) {
                window.location.reload();
            }
        }, {once: true});
    });
});
if (routeCoordinates.length > 1) {
    L.polyline(routeCoordinates, {
        color: '#1f5f43',
        weight: 4,
        opacity: 0.85,
    }).addTo(routeMap);
}
routeMarkers.forEach((marker) => {
    const point = [marker.latitude, marker.longitude];
    bounds.push(point);
    L.circleMarker(point, {
        radius: 10,
        color: '#1f5f43',
        fillColor: '#7bc47f',
        fillOpacity: 0.95,
        weight: 2.5,
    }).addTo(routeMap).bindPopup(`<strong>${marker.order}. ${marker.title}</strong><p>${marker.subtitle}</p>`);
});
if (bounds.length > 0) {
    routeMap.fitBounds(bounds, {padding: [28, 28]});
}
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
            <h1 class="hero-title hero-title-tight"><?= Html::encode($plan->name) ?></h1>
            <p class="hero-text"><?= Html::encode($plan->description ?: 'Sem descricao definida para este percurso.') ?></p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Paragens</span><strong><?= count($plan->routePlanPoints) ?></strong></article>
            <article class="detail-stat-card"><span>Mapa</span><strong>Selecao direta</strong></article>
            <article class="detail-stat-card"><span>Estado</span><strong>Planeado</strong></article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <section class="toolbar-card mb-4">
        <div class="toolbar-row">
            <div>
                <strong>Fluxo simplificado</strong>
                <p class="table-subtext mb-0">Seleciona diretamente uma observacao no mapa e usa "Quero passar aqui". A paragem entra logo neste percurso.</p>
            </div>
            <div class="toolbar-actions">
                <a class="btn btn-outline-brand" href="<?= Url::to(['route-plan/update', 'id' => $plan->route_plan_id]) ?>">Editar percurso</a>
                <?= Html::beginForm(['route-plan/delete', 'id' => $plan->route_plan_id], 'post') ?>
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
                <?php if (empty($plan->routePlanPoints)): ?>
                    <div class="empty-state-card compact-empty-state">
                        <h3>Sem paragens ainda</h3>
                        <p>Clica num ponto do mapa e usa "Quero passar aqui" para adicionar a primeira paragem.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($plan->routePlanPoints as $point): ?>
                        <?php $target = $point->savedVisitTarget; ?>
                        <article class="map-observation-item target-item-card">
                            <p class="species-scientific-name">Paragem <?= (int) $point->visit_order ?></p>
                            <h3><?= Html::encode($target?->getTargetTitle() ?? 'Alvo removido') ?></h3>
                            <p><?= Html::encode($target?->getTargetSubtitle() ?? 'Sem subtitulo') ?></p>
                            <div class="timeline-card-actions">
                                <?php if ($target?->observation_id !== null): ?>
                                    <a href="<?= Url::to(['observation/view', 'id' => $target->observation_id]) ?>">Abrir observacao</a>
                                <?php elseif ($target?->publication_id !== null): ?>
                                    <a href="<?= Url::to(['publication/view', 'id' => $target->publication_id]) ?>">Abrir publicacao</a>
                                <?php elseif ($target?->plant_species_id !== null): ?>
                                    <a href="<?= Url::to(['species/view', 'id' => $target->plant_species_id]) ?>">Abrir especie</a>
                                <?php endif; ?>
                                <?= Html::beginForm(['route-plan/remove-point', 'id' => $point->route_plan_point_id], 'post') ?>
                                    <?= Html::submitButton('Remover do percurso', ['class' => 'link-button']) ?>
                                <?= Html::endForm() ?>
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
                <h2>Outras formas de adicionar paragens</h2>
            </div>
        </div>
        <section class="toolbar-card mb-4">
            <div class="toolbar-row user-search-row">
                <?= Html::beginForm(['route-plan/view', 'id' => $plan->route_plan_id], 'get', ['class' => 'user-search-form']) ?>
                    <div class="user-search-input-wrap">
                        <?= Html::textInput('speciesQ', $speciesSearch, ['class' => 'form-control user-search-input', 'placeholder' => 'Pesquisar plantas por nome comum, cientifico, familia ou genero']) ?>
                    </div>
                    <div class="toolbar-actions">
                        <?= Html::submitButton('Pesquisar plantas', ['class' => 'btn btn-brand']) ?>
                        <?php if ($speciesSearch !== ''): ?>
                            <a class="btn btn-outline-brand" href="<?= Url::to(['route-plan/view', 'id' => $plan->route_plan_id]) ?>">Limpar</a>
                        <?php endif; ?>
                    </div>
                <?= Html::endForm() ?>
            </div>
        </section>
        <?php if (empty($plannableSpecies)): ?>
            <div class="empty-state-card">
                <h3>Sem plantas disponiveis</h3>
                <p>Nao encontrámos plantas com coordenadas para esta pesquisa, ou ja estao todas neste percurso.</p>
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
                            <?= Html::beginForm(['route-plan/add-species', 'id' => $plan->route_plan_id, 'speciesId' => $species->plant_species_id], 'post') ?>
                                <?= Html::hiddenInput('speciesQ', $speciesSearch) ?>
                                <?= Html::submitButton('Adicionar planta ao percurso', ['class' => 'btn btn-brand']) ?>
                            <?= Html::endForm() ?>
                            <a href="<?= Url::to(['species/view', 'id' => $species->plant_species_id]) ?>">Abrir especie</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
