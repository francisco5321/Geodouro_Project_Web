<?php

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
                <a href="${marker.detailUrl}">Abrir observacao</a>
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
if (visitBounds.length > 0) {
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
            <span class="eyebrow">Planeamento</span>
            <h1 class="hero-title hero-title-tight">Quero visitar</h1>
            <p class="hero-text">Tudo acontece aqui: clica nas observacoes do mapa, marca "Quero passar aqui" e depois cria logo o percurso com nome e descricao.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Alvos</span><strong><?= count($targets) ?></strong></article>
            <article class="detail-stat-card"><span>Percursos</span><strong><?= count($plans) ?></strong></article>
            <article class="detail-stat-card"><span>Mapa</span><strong>Observacoes clicaveis</strong></article>
            <article class="detail-stat-card"><span>Estado</span><strong>Planeado</strong></article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger mb-4"><?= Yii::$app->session->getFlash('error') ?></div>
    <?php endif; ?>

    <section class="toolbar-card mb-4 visit-route-builder-card">
        <div class="toolbar-row visit-route-builder-header">
            <div>
                <strong>Criar percurso a partir do mapa</strong>
                <p class="table-subtext mb-0">Os pontos que marcares com "Quero passar aqui" ficam guardados e entram logo no novo percurso pela ordem em que os foste escolhendo.</p>
            </div>
            <div class="toolbar-actions">
                <a class="btn btn-outline-brand" href="<?= Url::to(['route-plan/index']) ?>">Ver percursos</a>
            </div>
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
                        'placeholder' => 'Objetivo do percurso, especies a validar e notas para a visita de campo.',
                    ]) ?>
                </div>
            </div>
            <div class="visit-route-builder-actions">
                <span class="table-subtext">Ao criar o percurso, esta lista fica limpa e as paragens passam para o percurso. No mobile, a partida sera a localizacao atual.</span>
                <?= Html::submitButton('Criar percurso com os pontos selecionados', ['class' => 'btn btn-brand']) ?>
            </div>
        <?= Html::endForm() ?>
    </section>

    <section class="map-layout">
        <div id="visit-planner-map" class="leaflet-shell"></div>
        <aside class="map-sidebar">
            <h2>Lista guardada</h2>
            <div class="map-observation-list">
                <?php if (empty($targets)): ?>
                    <div class="empty-state-card compact-empty-state">
                        <h3>Ainda nao guardaste nada</h3>
                        <p>Abre um ponto do mapa e usa o botao "Quero passar aqui" para criares a tua lista.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($targets as $target): ?>
                        <article class="map-observation-item target-item-card">
                            <p class="species-scientific-name"><?= Html::encode($target->getTargetSubtitle()) ?></p>
                            <h3><?= Html::encode($target->getTargetTitle()) ?></h3>
                            <p>
                                <?php if ($target->getTargetType() === 'publication'): ?>
                                    Publicacao editorial
                                <?php elseif ($target->getTargetType() === 'observation'): ?>
                                    Observacao selecionada no mapa
                                <?php else: ?>
                                    Especie marcada
                                <?php endif; ?>
                            </p>
                            <div class="timeline-card-actions visit-target-actions">
                                <?php if ($target->observation_id !== null): ?>
                                    <a href="<?= Url::to(['observation/view', 'id' => $target->observation_id]) ?>">Abrir observacao</a>
                                <?php elseif ($target->publication_id !== null): ?>
                                    <a href="<?= Url::to(['publication/view', 'id' => $target->publication_id]) ?>">Abrir publicacao</a>
                                <?php elseif ($target->plant_species_id !== null): ?>
                                    <a href="<?= Url::to(['species/view', 'id' => $target->plant_species_id]) ?>">Abrir especie</a>
                                <?php endif; ?>
                                <?= Html::beginForm(['visit/remove', 'id' => $target->saved_visit_target_id], 'post') ?>
                                    <?= Html::submitButton('Remover', ['class' => 'link-button']) ?>
                                <?= Html::endForm() ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>
    </section>
</div>
