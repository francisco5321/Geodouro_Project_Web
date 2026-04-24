<?php

use app\models\Publication;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Publication $publication */

$this->title = $publication->title ?: 'Publicação #' . $publication->publication_id;
$imagePaths = $publication->getImageGalleryPaths();
$imageCount = count($imagePaths);
$observation = $publication->observation;
$coordinateLabel = $observation?->hasCoordinates()
    ? number_format((float) $observation->latitude, 5) . ', ' . number_format((float) $observation->longitude, 5)
    : 'Sem localização';

if ($observation?->hasCoordinates()) {
    $this->registerJs(<<<'JS'
const publicationLocationEl = document.getElementById('publication-location-name');
if (publicationLocationEl) {
    const latitude = publicationLocationEl.dataset.latitude;
    const longitude = publicationLocationEl.dataset.longitude;
    const fallback = publicationLocationEl.dataset.fallback || 'Localização registada';
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(latitude)}&lon=${encodeURIComponent(longitude)}&zoom=16&addressdetails=1&accept-language=pt`;

    fetch(url)
        .then((response) => response.ok ? response.json() : null)
        .then((data) => {
            const address = data && data.address ? data.address : {};
            const primary = address.road || address.neighbourhood || address.suburb || address.village || address.town || address.city || address.municipality || address.county;
            const secondary = address.city || address.town || address.village || address.municipality || address.county || address.state;
            const parts = [primary, secondary].filter((part, index, items) => part && items.indexOf(part) === index);
            publicationLocationEl.textContent = parts.length ? parts.join(', ') : (data && data.display_name ? data.display_name.split(',').slice(0, 2).join(',') : fallback);
        })
        .catch(() => {
            publicationLocationEl.textContent = fallback;
        });
}
JS);
}
?>
<div class="module-shell">
    <a class="back-link" href="<?= Url::to(['publication/index']) ?>">&larr; Voltar às publicações</a>

    <section class="publication-hero mb-4">
        <div class="publication-hero-media">
            <?php if (!empty($imagePaths)): ?>
                <img src="<?= Url::to(['media/publication-image', 'id' => $publication->publication_id, 'index' => 0]) ?>" alt="Capa da publicação <?= (int) $publication->publication_id ?>">
            <?php else: ?>
                <div class="publication-hero-placeholder">
                    <span class="eyebrow">Sem capa</span>
                    <strong>Conteúdo editorial</strong>
                </div>
            <?php endif; ?>
        </div>
        <div class="species-detail-copy">
            <span class="eyebrow">Publicação</span>
            <h1 class="hero-title hero-title-tight"><?= Html::encode($publication->title ?: 'Publicação botânica') ?></h1>
            <p class="species-detail-scientific"><?= Html::encode($publication->plantSpecies?->scientific_name ?? $observation?->getResolvedScientificName() ?? 'Sem espécie associada') ?></p>
            <div class="species-meta-row">
                <span class="species-meta-chip<?= $publication->isPublished() ? ' chip-highlight' : '' ?>"><?= Html::encode($publication->getStatusLabel()) ?></span>
                <span class="species-meta-chip"><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></span>
                <span class="species-meta-chip"><?= $imageCount ?> <?= $imageCount === 1 ? 'imagem' : 'imagens' ?></span>
            </div>
            <p class="hero-text"><?= Html::encode($publication->description ?: 'Sem texto editorial associado a esta publicação.') ?></p>
            <div class="hero-cta-row mt-4">
                <?php if ($publication->canBeManagedBy(Yii::$app->user->identity)): ?>
                    <a class="btn btn-brand" href="<?= Url::to(['publication/update', 'id' => $publication->publication_id]) ?>">Editar publicação</a>
                    <?php if (!$publication->isPublished()): ?>
                        <?= Html::beginForm(['publication/publish', 'id' => $publication->publication_id], 'post', ['class' => 'd-inline-block']) ?>
                            <?= Html::submitButton('Publicar agora', ['class' => 'btn btn-outline-brand']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <section class="detail-section">
        <div class="detail-split-grid detail-context-grid publication-context-grid">
            <article class="content-card detail-context-card">
                <div class="detail-card-title">
                    <span class="detail-card-icon"><i class="fas fa-circle-info" aria-hidden="true"></i></span>
                    <h2>Contexto</h2>
                </div>
                <div class="info-list detail-info-list">
                    <div class="detail-info-item"><span>Espécie</span><strong><?= Html::encode($publication->plantSpecies?->common_name ?: ($observation?->getResolvedCommonName() ?: 'Publicação botânica')) ?></strong></div>
                    <div class="detail-info-item"><span>Família</span><strong><?= Html::encode($publication->plantSpecies?->family ?: ($observation?->getResolvedFamily() ?: 'N/D')) ?></strong></div>
                    <div class="detail-info-item">
                        <span>Localização</span>
                        <?php if ($observation?->hasCoordinates()): ?>
                            <strong
                                id="publication-location-name"
                                data-latitude="<?= Html::encode((string) $observation->latitude) ?>"
                                data-longitude="<?= Html::encode((string) $observation->longitude) ?>"
                                data-fallback="<?= Html::encode($coordinateLabel) ?>"
                            >Localização registada</strong>
                            <small><?= Html::encode($coordinateLabel) ?></small>
                        <?php else: ?>
                            <strong>Sem localização</strong>
                        <?php endif; ?>
                    </div>
                    <div class="detail-info-item"><span>Wikipedia</span><strong><?= $observation?->enriched_wikipedia_url ? Html::a('Abrir referência', $observation->enriched_wikipedia_url, ['target' => '_blank', 'rel' => 'noopener']) : 'Sem referência' ?></strong></div>
                </div>
            </article>

            <article class="content-card content-card-soft detail-actions-card">
                <div class="detail-card-title">
                    <span class="detail-card-icon"><i class="fas fa-link" aria-hidden="true"></i></span>
                    <h2>Ligações</h2>
                </div>
                <div class="module-link-list detail-action-list">
                    <?php if ($publication->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $publication->plant_species_id]) ?>"><i class="fas fa-leaf" aria-hidden="true"></i><span>Abrir ficha da espécie</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                    <a href="<?= Url::to(['observation/view', 'id' => $publication->observation_id]) ?>"><i class="fas fa-newspaper" aria-hidden="true"></i><span>Abrir observação original</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    <a href="<?= Url::to(['map/index']) ?>"><i class="fas fa-map-location-dot" aria-hidden="true"></i><span>Ver no mapa</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </article>
        </div>
    </section>

    <?php if ($publication->canBeManagedBy(Yii::$app->user->identity)): ?>
        <section class="detail-section">
            <div class="content-card danger-zone-card">
                <h2>Gestão administrativa</h2>
                <p>Podes continuar a editar esta publicação ou removê-la por completo do portal.</p>
                <?= Html::beginForm(['publication/delete', 'id' => $publication->publication_id], 'post') ?>
                    <?= Html::submitButton('Eliminar publicação', ['class' => 'btn btn-outline-danger', 'data-confirm' => 'Queres mesmo eliminar esta publicação?']) ?>
                <?= Html::endForm() ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="detail-section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Galeria editorial</span>
                <h2>Imagens da publicação</h2>
            </div>
        </div>
        <?php if (empty($imagePaths)): ?>
            <div class="empty-state-card">
                <h3>Sem galeria acessível</h3>
                <p>Esta publicação ainda não tem imagens acessíveis pela web.</p>
            </div>
        <?php else: ?>
            <div class="observation-gallery-grid">
                <?php foreach ($imagePaths as $index => $path): ?>
                    <a class="observation-gallery-card" href="<?= Url::to(['media/publication-image', 'id' => $publication->publication_id, 'index' => $index]) ?>" target="_blank" rel="noopener">
                        <img src="<?= Url::to(['media/publication-image', 'id' => $publication->publication_id, 'index' => $index]) ?>" alt="Imagem da publicação <?= (int) $publication->publication_id ?>">
                        <span>Abrir imagem <?= $index + 1 ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
