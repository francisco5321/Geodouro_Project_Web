<?php

use app\models\Observation;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var Observation $observation */

$this->title = 'Observação #' . $observation->observation_id;
$statusLabel = $observation->is_published ? 'Publicada' : ($observation->sync_status === Observation::SYNC_SYNCED ? 'Sincronizada' : ($observation->sync_status === Observation::SYNC_FAILED ? 'Falha de sincronização' : 'Pendente'));
$imagePaths = $observation->getImageGalleryPaths();
$canCreatePublication = Yii::$app->user->identity?->isAdmin() || (int) $observation->user_id === (int) Yii::$app->user->id;
$canDeleteObservation = Yii::$app->user->identity?->isAdmin() ?? false;
$coordinateLabel = $observation->hasCoordinates()
    ? number_format((float) $observation->latitude, 5) . ', ' . number_format((float) $observation->longitude, 5)
    : 'Sem localização';

if ($observation->hasCoordinates()) {
    $this->registerJs(<<<'JS'
const locationEl = document.getElementById('observation-location-name');
if (locationEl) {
    const latitude = locationEl.dataset.latitude;
    const longitude = locationEl.dataset.longitude;
    const fallback = locationEl.dataset.fallback || 'Localização registada';
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(latitude)}&lon=${encodeURIComponent(longitude)}&zoom=16&addressdetails=1&accept-language=pt`;

    fetch(url)
        .then((response) => response.ok ? response.json() : null)
        .then((data) => {
            const address = data && data.address ? data.address : {};
            const primary = address.road || address.neighbourhood || address.suburb || address.village || address.town || address.city || address.municipality || address.county;
            const secondary = address.city || address.town || address.village || address.municipality || address.county || address.state;
            const parts = [primary, secondary].filter((part, index, items) => part && items.indexOf(part) === index);
            locationEl.textContent = parts.length ? parts.join(', ') : (data && data.display_name ? data.display_name.split(',').slice(0, 2).join(',') : fallback);
            locationEl.title = fallback;
        })
        .catch(() => {
            locationEl.textContent = fallback;
        });
}
JS, View::POS_END);
}
?>
<div class="module-shell">
    <a class="back-link" href="<?= Url::to(['observation/index']) ?>">&larr; Voltar às observações</a>

    <section class="species-detail-hero mb-4">
        <div class="species-detail-copy">
            <span class="eyebrow">Observação de campo</span>
            <h1 class="hero-title hero-title-tight"><?= Html::encode($observation->getResolvedCommonName() ?: 'Observação botânica') ?></h1>
            <p class="species-detail-scientific"><?= Html::encode($observation->getResolvedScientificName() ?: 'Sem classificação enriquecida') ?></p>
            <div class="species-meta-row">
                <span class="species-meta-chip"><?= Html::encode($statusLabel) ?></span>
                <span class="species-meta-chip"><?= Html::encode($observation->getResolvedFamily() ?: 'Família desconhecida') ?></span>
                <?php if ($observation->publication !== null): ?><span class="species-meta-chip chip-highlight">Já publicada</span><?php endif; ?>
            </div>
            <p class="hero-text"><?= Html::encode($observation->notes ?: 'Sem notas de campo registadas para esta observação.') ?></p>
            <div class="hero-cta-row mt-4">
                <?php if ($canDeleteObservation): ?>
                    <?= Html::a(
                        '<i class="fas fa-trash" aria-hidden="true"></i> Remover observação',
                        ['observation/delete', 'id' => $observation->observation_id],
                        [
                            'class' => 'btn btn-danger',
                            'data-method' => 'post',
                            'data-confirm' => 'Tens a certeza que queres remover esta observação? Esta ação também remove publicações e imagens associadas.',
                        ]
                    ) ?>
                <?php endif; ?>
                <?php if ($observation->publication !== null): ?>
                    <a class="btn btn-outline-brand" href="<?= Url::to(['publication/view', 'id' => $observation->publication->publication_id]) ?>">
                        <i class="fas fa-newspaper" aria-hidden="true"></i>
                        Abrir publicação
                    </a>
                <?php elseif ($canCreatePublication): ?>
                    <a class="btn btn-outline-brand" href="<?= Url::to(['publication/create', 'observationId' => $observation->observation_id]) ?>">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Criar publicação
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Confiança</span><strong><?= $observation->confidence !== null ? (int) round($observation->confidence * 100) . '%' : 'N/D' ?></strong></article>
            <article class="detail-stat-card"><span>Autor</span><strong><?= Html::encode($observation->user?->getFullName() ?? 'Sistema') ?></strong></article>
            <article class="detail-stat-card"><span>Data</span><strong><?= Html::encode(Yii::$app->formatter->asDate($observation->observed_at, 'php:d/m/Y')) ?></strong></article>
            <article class="detail-stat-card"><span>Imagens</span><strong><?= count($imagePaths) ?></strong></article>
        </div>
    </section>

    <section class="detail-section">
        <div class="detail-split-grid detail-context-grid">
            <article class="content-card detail-context-card">
                <div class="detail-card-title">
                    <span class="detail-card-icon"><i class="fas fa-circle-info" aria-hidden="true"></i></span>
                    <h2>Contexto</h2>
                </div>
                <div class="info-list detail-info-list">
                    <div class="detail-info-item"><span>Espécie</span><strong><?= Html::encode($observation->getResolvedCommonName() ?: 'Observação botânica') ?></strong></div>
                    <div class="detail-info-item"><span>Família</span><strong><?= Html::encode($observation->getResolvedFamily() ?: 'N/D') ?></strong></div>
                    <div class="detail-info-item">
                        <span>Localização</span>
                        <?php if ($observation->hasCoordinates()): ?>
                            <strong
                                id="observation-location-name"
                                data-latitude="<?= Html::encode((string) $observation->latitude) ?>"
                                data-longitude="<?= Html::encode((string) $observation->longitude) ?>"
                                data-fallback="<?= Html::encode($coordinateLabel) ?>"
                            >Localização registada</strong>
                            <small><?= Html::encode($coordinateLabel) ?></small>
                        <?php else: ?>
                            <strong>Sem localização</strong>
                        <?php endif; ?>
                    </div>
                    <div class="detail-info-item"><span>Wikipedia</span><strong><?= $observation->enriched_wikipedia_url ? Html::a('Abrir referência', $observation->enriched_wikipedia_url, ['target' => '_blank', 'rel' => 'noopener']) : 'Sem referência' ?></strong></div>
                </div>
            </article>
            <article class="content-card content-card-soft detail-actions-card">
                <div class="detail-card-title">
                    <span class="detail-card-icon"><i class="fas fa-link" aria-hidden="true"></i></span>
                    <h2>Ligações</h2>
                </div>
                <div class="module-link-list detail-action-list">
                    <?php if ($observation->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $observation->plant_species_id]) ?>"><i class="fas fa-leaf" aria-hidden="true"></i><span>Abrir ficha da espécie</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                    <?php if ($observation->publication !== null): ?><a href="<?= Url::to(['publication/view', 'id' => $observation->publication->publication_id]) ?>"><i class="fas fa-newspaper" aria-hidden="true"></i><span>Abrir publicação associada</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                    <?php if ($observation->hasCoordinates()): ?><a href="<?= Url::to(['map/index']) ?>"><i class="fas fa-map-location-dot" aria-hidden="true"></i><span>Ver no mapa</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                </div>
            </article>
        </div>
    </section>

    <section class="detail-section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Galeria</span>
                <h2>Imagens da observação</h2>
            </div>
            <?php if (!empty($imagePaths)): ?>
                <span class="section-count"><?= count($imagePaths) ?> <?= count($imagePaths) === 1 ? 'imagem' : 'imagens' ?></span>
            <?php endif; ?>
        </div>
        <?php if (empty($imagePaths)): ?>
            <div class="empty-state-card">
                <h3>Sem imagem acessível</h3>
                <p>Esta observação não tem ficheiros de imagem que a web consiga servir neste momento.</p>
            </div>
        <?php else: ?>
            <div class="observation-gallery-grid">
                <?php foreach ($imagePaths as $index => $path): ?>
                    <a class="observation-gallery-card" href="<?= Url::to(['media/observation-image', 'id' => $observation->observation_id, 'index' => $index]) ?>" target="_blank" rel="noopener">
                        <img src="<?= Url::to(['media/observation-image', 'id' => $observation->observation_id, 'index' => $index]) ?>" alt="Imagem da observação <?= (int) $observation->observation_id ?>">
                        <span>Abrir imagem <?= $index + 1 ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
