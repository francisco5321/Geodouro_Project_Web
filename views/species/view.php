<?php

use app\models\Observation;
use app\models\PlantSpecies;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var PlantSpecies $species */
/** @var Observation[] $observations */
/** @var yii\data\Pagination $pagination */
/** @var array $galleryImages */
/** @var string|null $locationSummary */
/** @var array|null $locationBounds */
/** @var array $stats */

$this->title = $species->scientific_name;
$heroImage = $galleryImages[0] ?? null;
$heroImageUrl = $heroImage !== null
    ? (isset($heroImage['path'])
        ? Url::to(['media/upload-path', 'path' => $heroImage['path']])
        : Url::to(['media/observation-image', 'id' => $heroImage['observationId'], 'index' => $heroImage['imageIndex']]))
    : null;
$commonName = $species->common_name ?: 'Sem nome comum registado';
$imageCount = (int) $species->image_count;
$observationCount = (int) ($stats['observationsCount'] ?? 0);
$syncedCount = (int) ($stats['syncedCount'] ?? 0);
$publishedCount = (int) ($stats['publishedCount'] ?? 0);
$hasLocationBounds = is_array($locationBounds ?? null)
    && isset(
        $locationBounds['minLatitude'],
        $locationBounds['maxLatitude'],
        $locationBounds['minLongitude'],
        $locationBounds['maxLongitude']
    );

if ($hasLocationBounds) {
    $this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
    $this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', ['position' => View::POS_END]);
}
?>

<div class="species-showcase">
    <a class="back-link species-back-link" href="<?= Url::to(['species/index']) ?>">&larr; Voltar ao catalogo</a>

    <section class="species-stage">
        <div class="species-stage-media">
            <?php if ($heroImageUrl !== null): ?>
                <img
                    src="<?= $heroImageUrl ?>"
                    alt="Imagem principal de <?= Html::encode($species->scientific_name) ?>"
                    class="species-stage-photo"
                >
            <?php else: ?>
                <div class="species-stage-placeholder">
                    <i class="fas fa-leaf" aria-hidden="true"></i>
                    <span>Sem imagem principal</span>
                </div>
            <?php endif; ?>

            <div class="species-stage-overlay"></div>
            <div class="species-stage-badge">
                <?= $imageCount ?> <?= $imageCount === 1 ? 'imagem' : 'imagens' ?>
            </div>
        </div>

        <article class="species-stage-panel">
            <div class="species-stage-heading">
                <span class="species-kicker">Ficha de Especie</span>
                <h1><?= Html::encode($species->scientific_name) ?></h1>
                <p><?= Html::encode($commonName) ?> · <?= Html::encode($species->family) ?></p>
            </div>

            <div class="species-stage-tags">
                <span class="species-tag">
                    <i class="fas fa-sitemap" aria-hidden="true"></i>
                    <?= Html::encode($species->family) ?>
                </span>
                <span class="species-tag">
                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                    <?= Html::encode($species->genus) ?>
                </span>
                <span class="species-tag">
                    <i class="fas fa-dna" aria-hidden="true"></i>
                    <?= Html::encode($species->species) ?>
                </span>
            </div>

            <p class="species-stage-copy">
                <?= Html::encode($species->description ?: 'Esta ficha resume a espécie, as imagens recolhidas e as observações recentes sincronizadas entre mobile e web.') ?>
            </p>

            <?php if (Yii::$app->user->identity?->isAdmin()): ?>
                <div class="hero-cta-row mt-3">
                    <a class="btn btn-outline-brand" href="<?= Url::to(['species/update', 'id' => $species->plant_species_id]) ?>">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                        Editar espécie
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($galleryImages)): ?>
                <div class="species-filmstrip" aria-label="Galeria da especie">
                    <?php foreach ($galleryImages as $image): ?>
                        <?php $imageUrl = isset($image['path'])
                            ? Url::to(['media/upload-path', 'path' => $image['path']])
                            : Url::to(['media/observation-image', 'id' => $image['observationId'], 'index' => $image['imageIndex']]); ?>
                        <a
                            href="<?= $imageUrl ?>"
                            target="_blank"
                            rel="noopener"
                            class="species-filmstrip-item"
                        >
                            <img
                                src="<?= $imageUrl ?>"
                                alt="Imagem da galeria de <?= Html::encode($species->scientific_name) ?>"
                                loading="lazy"
                            >
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="species-metrics-grid">
                <article class="species-metric-card">
                    <strong><?= $observationCount ?></strong>
                    <span>Observ.</span>
                </article>
                <article class="species-metric-card">
                    <strong><?= $imageCount ?></strong>
                    <span>Imagens</span>
                </article>
                <article class="species-metric-card">
                    <strong><?= $syncedCount ?></strong>
                    <span>Sincron.</span>
                </article>
                <article class="species-metric-card">
                    <strong><?= $publishedCount ?></strong>
                    <span>Publicas</span>
                </article>
            </div>
        </article>
    </section>

    <?php if ($locationSummary !== null || $hasLocationBounds): ?>
        <section class="species-section">
            <article class="species-location-panel">
                <div class="species-location-icon">
                    <i class="fas fa-location-dot" aria-hidden="true"></i>
                </div>
                <div class="species-location-copy">
                    <span class="species-kicker">Localização</span>
                    <p>
                        Localizações registadas em <?= (int) ($locationBounds['count'] ?? $observationCount) ?>
                        <?= ((int) ($locationBounds['count'] ?? $observationCount) === 1) ? 'observacao' : 'observacoes' ?>.
                    </p>
                    <?php if ($hasLocationBounds): ?>
                        <div
                            id="species-location-map"
                            class="leaflet-shell species-location-map"
                            data-bounds="<?= Html::encode(Json::encode($locationBounds)) ?>"
                        ></div>
                    <?php elseif ($locationSummary !== null): ?>
                        <p><?= Html::encode($locationSummary) ?></p>
                    <?php endif; ?>
                </div>
            </article>
        </section>
    <?php endif; ?>

    <section class="species-section" id="species-recent-observations">
        <div class="species-section-heading">
            <div>
                <span class="species-kicker">Observações Recentes</span>
                <h2>Registos mais recentes desta especie</h2>
            </div>
            <span class="species-section-count"><?= $observationCount ?> total</span>
        </div>

        <?php if (empty($observations)): ?>
            <div class="empty-state-card">
                <h3>Sem observações associadas</h3>
                <p>Quando a app mobile sincronizar registos desta especie, eles vao aparecer aqui automaticamente.</p>
            </div>
        <?php else: ?>
            <div class="species-observation-stack">
                <?php foreach ($observations as $observation): ?>
                    <?php
                    $statusLabel = $observation->is_published
                        ? 'Publicada'
                        : ($observation->sync_status === Observation::SYNC_SYNCED ? 'Sincronizada' : ($observation->sync_status === Observation::SYNC_FAILED ? 'Falha de sincronização' : 'Pendente'));
                    $confidence = $observation->confidence !== null ? (int) round($observation->confidence * 100) : null;
                    $thumbPath = $observation->getImageGalleryPaths();
                    $thumbUrl = !empty($thumbPath)
                        ? ($observation->observation_id !== null
                            ? Url::to(['media/observation-image', 'id' => $observation->observation_id, 'index' => 0])
                            : Url::to(['media/upload-path', 'path' => $thumbPath[0]]))
                        : null;
                    $observationUrl = $observation->observation_id !== null
                        ? Url::to(['observation/view', 'id' => $observation->observation_id])
                        : null;
                    ?>
                    <<?= $observationUrl !== null ? 'a' : 'div' ?> class="species-observation-row"<?= $observationUrl !== null ? ' href="' . $observationUrl . '"' : '' ?>>
                        <div class="species-observation-media">
                            <?php if ($thumbUrl !== null): ?>
                                <img
                                    src="<?= $thumbUrl ?>"
                                    alt="Imagem da observação <?= (int) $observation->observation_id ?>"
                                    loading="lazy"
                                >
                            <?php else: ?>
                                <span class="species-observation-fallback">
                                    <i class="fas fa-image" aria-hidden="true"></i>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="species-observation-body">
                            <strong class="species-observation-title">
                                <?= Html::encode($observation->getResolvedCommonName() ?: 'Observação botânica') ?>
                            </strong>
                            <p class="species-observation-date">
                                <?= Html::encode(Yii::$app->formatter->asDatetime($observation->observed_at, 'php:d/m/Y · H:i')) ?>
                            </p>

                            <?php if ($observation->publication !== null): ?>
                                <p class="species-observation-publisher">
                                    <?= Html::encode('Publicado por ' . ($observation->publication->user?->getFullName() ?? 'Sistema')) ?>
                                </p>
                            <?php endif; ?>

                            <div class="species-observation-meta">
                                <span class="species-mini-badge">
                                    <?= $confidence !== null ? 'Conf. ' . $confidence . '%' : 'Conf. N/D' ?>
                                </span>
                                <span class="species-mini-badge<?= $observation->is_published ? ' is-published' : '' ?>">
                                    <?= Html::encode($statusLabel) ?>
                                </span>
                            </div>
                        </div>

                        <div class="species-observation-arrow">
                            <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        </div>
                    </<?= $observationUrl !== null ? 'a' : 'div' ?>>
                <?php endforeach; ?>
            </div>

            <div class="catalog-pagination">
                <?= LinkPager::widget([
                    'pagination' => $pagination,
                    'options' => ['class' => 'pagination justify-content-center mb-0'],
                    'linkOptions' => ['class' => 'page-link'],
                    'pageCssClass' => 'page-item',
                    'prevPageCssClass' => 'page-item',
                    'nextPageCssClass' => 'page-item',
                    'disabledPageCssClass' => 'page-item disabled',
                    'activePageCssClass' => 'page-item active',
                    'prevPageLabel' => '‹',
                    'nextPageLabel' => '›',
                    'hideOnSinglePage' => true,
                    'disabledListItemSubTagOptions' => ['class' => 'd-none'],
                ]) ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
$this->registerJs(<<<'JS'
const speciesPaginationStorageKey = 'species-view-scroll-position';

document.querySelectorAll('.catalog-pagination a.page-link').forEach((link) => {
    link.addEventListener('click', () => {
        sessionStorage.setItem(speciesPaginationStorageKey, String(window.scrollY));
    });
});

const savedSpeciesScrollPosition = sessionStorage.getItem(speciesPaginationStorageKey);
if (savedSpeciesScrollPosition !== null) {
    sessionStorage.removeItem(speciesPaginationStorageKey);
    const targetScrollY = Number(savedSpeciesScrollPosition);
    requestAnimationFrame(() => {
        window.scrollTo(0, targetScrollY);
        requestAnimationFrame(() => {
            window.scrollTo(0, targetScrollY);
        });
    });
}

const speciesLocationMapEl = document.getElementById('species-location-map');
if (speciesLocationMapEl && typeof L !== 'undefined') {
    const boundsData = JSON.parse(speciesLocationMapEl.dataset.bounds || '{}');
    const minLatitude = Number(boundsData.minLatitude);
    const maxLatitude = Number(boundsData.maxLatitude);
    const minLongitude = Number(boundsData.minLongitude);
    const maxLongitude = Number(boundsData.maxLongitude);

    if (
        !Number.isNaN(minLatitude)
        && !Number.isNaN(maxLatitude)
        && !Number.isNaN(minLongitude)
        && !Number.isNaN(maxLongitude)
    ) {
        const centerLatitude = (minLatitude + maxLatitude) / 2;
        const centerLongitude = (minLongitude + maxLongitude) / 2;
        const center = L.latLng(centerLatitude, centerLongitude);
        const southWest = L.latLng(minLatitude, minLongitude);
        const northEast = L.latLng(maxLatitude, maxLongitude);
        const corners = [
            L.latLng(minLatitude, minLongitude),
            L.latLng(minLatitude, maxLongitude),
            L.latLng(maxLatitude, minLongitude),
            L.latLng(maxLatitude, maxLongitude),
        ];
        const radius = Math.max(
            ...corners.map((corner) => center.distanceTo(corner)),
            35
        );

        const map = L.map(speciesLocationMapEl, {
            zoomControl: true,
            scrollWheelZoom: false,
            dragging: true,
            doubleClickZoom: true,
            touchZoom: true,
            boxZoom: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        L.circle([centerLatitude, centerLongitude], {
            radius: radius,
            color: '#20593f',
            weight: 2,
            fillColor: '#7fc084',
            fillOpacity: 0.22,
        }).addTo(map);

        L.marker([centerLatitude, centerLongitude]).addTo(map)
            .bindPopup('Centro aproximado da area observada');

        map.fitBounds([southWest, northEast], {padding: [28, 28]});
        setTimeout(() => map.invalidateSize(), 0);
    }
}
JS);
?>

<style>
.species-location-map {
    height: 260px;
    min-height: 260px;
    margin-top: 1rem;
    border-radius: 20px;
    overflow: hidden;
}
</style>
