<?php

use app\components\StatCard;
use app\models\RoutePlan;
use app\models\SavedVisitTarget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var SavedVisitTarget[] $targets */
/** @var RoutePlan[] $plans */
/** @var RoutePlan $newPlan */
/** @var string $markersJson */

$this->title = 'Quero visitar';
$this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
$this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['position' => View::POS_END]);
$toggleUrl = Url::to(['visit/toggle-observation']);
$js = <<<'JS'
const visitMarkers = __MARKERS__ || [];
const toggleObservationUrl = '__TOGGLE_URL__';
const visitMap = L.map('visit-planner-map').setView([41.3, -7.7], 8);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const csrfParam = document.querySelector('meta[name="csrf-param"]')?.content || '_csrf';

// Restaurar estado do mapa se existir
let mapStateRestored = false;
const savedMapState = sessionStorage.getItem('visitMapState');
if (savedMapState) {
    try {
        const state = JSON.parse(savedMapState);
        visitMap.setView(state.center, state.zoom);
        mapStateRestored = true;
        sessionStorage.removeItem('visitMapState');
    } catch (e) {
        console.error('Erro ao restaurar estado do mapa:', e);
    }
}

// Restaurar posição de scroll após a página estar carregada
function restoreVisitScrollPosition() {
    const savedScrollPosition = sessionStorage.getItem('visitScrollPosition');
    if (savedScrollPosition) {
        try {
            const scrollPos = JSON.parse(savedScrollPosition);
            window.scrollTo(scrollPos.x, scrollPos.y);
            sessionStorage.removeItem('visitScrollPosition');
        } catch (e) {
            console.error('Erro ao restaurar posição de scroll:', e);
        }
    }
}

// Tentar restaurar múltiplas vezes para garantir
document.addEventListener('DOMContentLoaded', () => {
    restoreVisitScrollPosition();
    requestAnimationFrame(restoreVisitScrollPosition);
    setTimeout(restoreVisitScrollPosition, 50);
    setTimeout(restoreVisitScrollPosition, 200);
    setTimeout(restoreVisitScrollPosition, 500);
});
window.addEventListener('load', () => {
    requestAnimationFrame(restoreVisitScrollPosition);
    setTimeout(restoreVisitScrollPosition, 100);
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(visitMap);
const visitBounds = [];
function buildPopup(marker) {
    const buttonLabel = marker.isSaved ? 'Remover da visita' : 'Quero passar aqui';
    return `
        <div class="map-popup map-popup-rich">
            <strong>${marker.title}</strong>
            <p>${marker.scientificName}</p>
            <p>${marker.status}</p>
            <div class="map-popup-actions">
                <a href="${marker.detailUrl}">Abrir observação</a>
                <button type="button" class="map-popup-button" data-observation-id="${marker.id}">${buttonLabel}</button>
            </div>
        </div>
    `;
}
visitMarkers.forEach((marker) => {
    const point = [marker.latitude, marker.longitude];
    visitBounds.push(point);
    const circle = L.circleMarker(point, {
        radius: marker.isSaved ? 9 : 6,
        color: marker.isSaved ? '#1f5f43' : '#6d7f72',
        fillColor: marker.isSaved ? '#5aa05a' : '#d7e0d5',
        fillOpacity: marker.isSaved ? 0.95 : 0.55,
        weight: marker.isSaved ? 2 : 1.5,
    }).addTo(visitMap).bindPopup(buildPopup(marker));
    circle.on('popupopen', () => {
        const button = document.querySelector(`.map-popup-button[data-observation-id="${marker.id}"]`);
        if (!button) {
            return;
        }
        button.addEventListener('click', async () => {
            // Guardar estado do mapa antes de fazer reload
            const mapState = {
                zoom: visitMap.getZoom(),
                center: visitMap.getCenter()
            };
            sessionStorage.setItem('visitMapState', JSON.stringify(mapState));
            
            // Guardar posição de scroll
            const scrollPosition = {
                x: window.scrollX,
                y: window.scrollY
            };
            sessionStorage.setItem('visitScrollPosition', JSON.stringify(scrollPosition));
            
            const body = new URLSearchParams();
            body.append(csrfParam, csrfToken);
            const response = await fetch(`${toggleObservationUrl}?id=${marker.id}`, {
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

document.addEventListener('click', async (event) => {
    const button = event.target.closest('.js-visit-observation-toggle');
    if (!button) {
        return;
    }

    event.preventDefault();
    button.disabled = true;

    const mapState = {
        zoom: visitMap.getZoom(),
        center: visitMap.getCenter()
    };
    sessionStorage.setItem('visitMapState', JSON.stringify(mapState));

    const scrollPosition = {
        x: window.scrollX,
        y: window.scrollY
    };
    sessionStorage.setItem('visitScrollPosition', JSON.stringify(scrollPosition));

    const body = new URLSearchParams();
    body.append(csrfParam, csrfToken);
    const response = await fetch(`${toggleObservationUrl}?id=${button.dataset.observationId}`, {
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
});

if (visitBounds.length > 0 && !mapStateRestored) {
    visitMap.fitBounds(visitBounds, {padding: [28, 28]});
}

JS;
$js = str_replace('__MARKERS__', $markersJson, $js);
$js = str_replace('__TOGGLE_URL__', $toggleUrl, $js);
$this->registerJs($js, View::POS_END);
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-route" aria-hidden="true"></i>
                Planeamento
            </span>
            <h1 class="hero-title hero-title-tight">Quero Visitar</h1>
            <p class="hero-text">Marca observações no mapa, seleciona "Quero passar aqui" e cria percursos com nome e descrição para planear a tua exploração do território.</p>
        </div>
        <div class="detail-stat-grid">
            <?= StatCard::widget([
                'label' => 'Alvos',
                'value' => (int) count($targets),
                'icon' => 'fas fa-map-pin',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Percursos',
                'value' => (int) count($plans),
                'icon' => 'fas fa-route',
            ]) ?>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert-success-custom mb-4">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert-danger-custom mb-4">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <section class="catalog-toolbar mb-4 visit-route-builder-card">
        <div class="toolbar-header">
            <h2 class="section-title">
                <i class="fas fa-plus-circle" aria-hidden="true"></i>
                Criar Percurso
            </h2>
            <p class="section-description mb-0">Os pontos que marcares com "Quero passar aqui" ficam guardados e entram no novo percurso pela ordem de escolha.</p>
        </div>
        <div class="toolbar-actions">
            <a class="btn btn-outline" href="<?= Url::to(['route-plan/index']) ?>">
                <i class="fas fa-eye" aria-hidden="true"></i>
                Ver Percursos
            </a>
        </div>
        <?= Html::beginForm(['visit/create-route'], 'post', ['class' => 'visit-route-builder-form']) ?>
            <div class="visit-route-builder-grid">
                <div>
                    <?= Html::activeLabel($newPlan, 'name', ['class' => 'form-label']) ?>
                    <?= Html::activeTextInput($newPlan, 'name', [
                        'class' => 'form-control',
                        'placeholder' => 'Ex.: Plantas ribeirinhas do Pocinho',
                        'maxlength' => true,
                    ]) ?>
                </div>
                <div>
                    <?= Html::activeLabel($newPlan, 'description', ['class' => 'form-label']) ?>
                    <?= Html::activeTextarea($newPlan, 'description', [
                        'class' => 'form-control',
                        'rows' => 2,
                        'placeholder' => 'Objetivo do percurso, espécies a validar e notas para a visita de campo.',
                    ]) ?>
                </div>
            </div>
            <div class="visit-route-builder-actions">
                <span class="section-description mb-0">Ao criar o percurso, esta lista fica limpa e as paragens passam para o percurso. Na aplicação mobile, a partida será a localização atual.</span>
                <?= Html::submitButton('Criar Percurso com os Pontos Selecionados', ['class' => 'btn btn-brand']) ?>
            </div>
        <?= Html::endForm() ?>
    </section>

    <section class="map-layout">
        <div id="visit-planner-map" class="leaflet-shell"></div>
        <aside class="map-sidebar">
            <h2 class="sidebar-title">
                <i class="fas fa-bookmark" aria-hidden="true"></i>
                Lista Guardada
            </h2>
            <div class="map-observation-list">
                <?php if (empty($targets)): ?>
                    <div class="empty-state-card compact-empty-state">
                        <h3>Ainda não guardaste nada</h3>
                        <p>Abre um ponto do mapa e usa o botao "Quero passar aqui" para criares a tua lista.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($targets as $target): ?>
                        <article class="map-observation-item target-item-card">
                            <p class="species-scientific-name"><?= Html::encode($target->getTargetSubtitle()) ?></p>
                            <h3><?= Html::encode($target->getTargetTitle()) ?></h3>
                            <p>
                                <?php if ($target->getTargetType() === 'publication'): ?>
                                    Publicação editorial
                                <?php elseif ($target->getTargetType() === 'observation'): ?>
                                    Observação selecionada no mapa
                                <?php else: ?>
                                    Espécie marcada
                                <?php endif; ?>
                            </p>
                            <div class="timeline-card-actions visit-target-actions">
                                <?php if ($target->observation_id !== null): ?>
                                    <a href="<?= Url::to(['observation/view', 'id' => $target->observation_id]) ?>">Abrir observação</a>
                                <?php elseif ($target->publication_id !== null): ?>
                                    <a href="<?= Url::to(['publication/view', 'id' => $target->publication_id]) ?>">Abrir publicação</a>
                                <?php elseif ($target->plant_species_id !== null): ?>
                                    <a href="<?= Url::to(['species/view', 'id' => $target->plant_species_id]) ?>">Abrir espécie</a>
                                <?php endif; ?>
                                <?php if ($target->observation_id !== null): ?>
                                    <button type="button" class="link-button js-visit-observation-toggle" data-observation-id="<?= (int) $target->observation_id ?>">Remover</button>
                                <?php else: ?>
                                    <?= Html::beginForm(['visit/remove', 'id' => $target->saved_visit_target_id], 'post') ?>
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
</div>
