<?php

use app\models\Observation;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var Observation $observation */
/** @var string $returnUrl */

$this->title = 'ObservaÃ§Ã£o #' . $observation->observation_id;
$statusLabel = $observation->is_published
    ? 'Publicada'
    : ($observation->sync_status === Observation::SYNC_SYNCED
        ? 'Sincronizada'
        : ($observation->sync_status === Observation::SYNC_FAILED ? 'Falha de sincronizaÃ§Ã£o' : 'Pendente'));
$statusLabel = $observation->needsManualReview() ? 'RevisÃ£o manual' : $statusLabel;
$imagePaths = $observation->getImageGalleryPaths();
$canCreatePublication = Yii::$app->user->identity?->isAdmin() || (int) $observation->user_id === (int) Yii::$app->user->id;
$canDeleteObservation = Yii::$app->user->identity?->isAdmin() ?? false;
$canReviewObservation = $observation->needsManualReview() && (Yii::$app->user->identity?->isAdmin() ?? false);
$canRequestManualReview = !Yii::$app->user->isGuest
    && !$observation->needsManualReview()
    && !$observation->is_published
    && ((Yii::$app->user->identity?->isAdmin() ?? false) || (int) $observation->user_id === (int) Yii::$app->user->id);
$manualReviewTriggeredByUser = $observation->needsManualReview()
    && trim((string) $observation->predicted_scientific_name) !== ''
    && !in_array(
        trim((string) $observation->predicted_scientific_name),
        ['Nao conhecemos essa planta', 'NÃ£o conhecemos essa planta'],
        true
    );
$manualReviewMessage = $manualReviewTriggeredByUser
    ? 'O MobileNet identificou a planta, mas o utilizador decidiu enviar para a administraÃ§Ã£o.'
    : 'O YOLO detetou uma planta, mas o MobileNet nÃ£o conseguiu reconhecer a espÃ©cie. Esta observaÃ§Ã£o estÃ¡ na fila de revisÃ£o manual.';
$coordinateLabel = $observation->hasCoordinates()
    ? number_format((float) $observation->latitude, 5) . ', ' . number_format((float) $observation->longitude, 5)
    : 'Sem localizaÃ§Ã£o';

if ($observation->hasCoordinates()) {
    $this->registerJs(<<<'JS'
const locationEl = document.getElementById('observation-location-name');
if (locationEl) {
    const latitude = locationEl.dataset.latitude;
    const longitude = locationEl.dataset.longitude;
    const fallback = locationEl.dataset.fallback || 'LocalizaÃ§Ã£o registada';
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

if ($observation->needsManualReview()) {
    $manualReviewMessageJson = json_encode($manualReviewMessage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $this->registerJs(<<<JS
const manualReviewMessage = $manualReviewMessageJson;
const manualReviewTextEl = document.querySelector('.species-detail-copy .hero-text');
if (manualReviewTextEl) {
    manualReviewTextEl.textContent = manualReviewMessage;
}
JS, View::POS_END);
}
?>
<div class="module-shell">
    <a class="back-link" href="<?= Html::encode($returnUrl) ?>">&larr; Voltar as observaÃ§Ãµes</a>

    <section class="species-detail-hero mb-4">
        <div class="species-detail-copy">
            <span class="eyebrow">ObservaÃ§Ã£o de campo</span>
            <h1 class="hero-title hero-title-tight"><?= Html::encode($observation->needsManualReview() ? 'ObservaÃ§Ã£o por identificar' : ($observation->getResolvedCommonName() ?: 'ObservaÃ§Ã£o botÃ¢nica')) ?></h1>
            <p class="species-detail-scientific"><?= Html::encode($observation->getResolvedScientificName() ?: 'Sem classificaÃ§Ã£o enriquecida') ?></p>
            <div class="species-meta-row">
                <span class="species-meta-chip"><?= Html::encode($statusLabel) ?></span>
                <span class="species-meta-chip"><?= Html::encode($observation->getResolvedFamily() ?: 'FamÃ­lia desconhecida') ?></span>
                <?php if ($observation->publication?->publication_id !== null): ?><span class="species-meta-chip chip-highlight">JÃ¡ publicada</span><?php endif; ?>
            </div>
            <?php if ($observation->needsManualReview()): ?>
                <p class="hero-text">O YOLO detetou uma planta, mas o MobileNet nÃ£o conseguiu reconhecer a espÃ©cie. Esta observaÃ§Ã£o estÃ¡ na fila de revisÃ£o manual.</p>
            <?php elseif ($canRequestManualReview): ?>
                <p class="hero-text">A previsÃ£o estÃ¡ incorreta? Podes enviar esta observaÃ§Ã£o para a administraÃ§Ã£o rever manualmente, tal como acontece quando o MobileNet nÃ£o consegue identificar a planta.</p>
            <?php endif; ?>
            <p class="hero-text"><?= Html::encode($observation->notes ?: 'Sem notas de campo registadas para esta observaÃ§Ã£o.') ?></p>
            <div class="hero-cta-row mt-4">
                <?php if ($canReviewObservation && $observation->observation_id !== null): ?>
                    <a class="btn btn-brand" href="<?= Url::to(['observation/update', 'id' => $observation->observation_id]) ?>">
                        <i class="fas fa-user-check" aria-hidden="true"></i>
                        Completar identificaÃ§Ã£o
                    </a>
                <?php endif; ?>
                <?php if ($canRequestManualReview && $observation->observation_id !== null): ?>
                    <?= Html::a(
                        '<i class="fas fa-flag" aria-hidden="true"></i> Enviar para a administraÃ§Ã£o',
                        ['observation/request-review', 'id' => $observation->observation_id],
                        [
                            'class' => 'btn btn-outline-brand',
                            'data-method' => 'post',
                            'data-confirm' => 'Queres enviar esta observaÃ§Ã£o para revisÃ£o manual da administraÃ§Ã£o?',
                        ]
                    ) ?>
                <?php endif; ?>
                <?php if ($canDeleteObservation && $observation->observation_id !== null): ?>
                    <?= Html::a(
                        '<i class="fas fa-trash" aria-hidden="true"></i> Remover observaÃ§Ã£o',
                        ['observation/delete', 'id' => $observation->observation_id],
                        [
                            'class' => 'btn btn-danger',
                            'data-method' => 'post',
                            'data-confirm' => 'Tens a certeza que queres remover esta observaÃ§Ã£o? Esta aÃ§Ã£o tambÃ©m remove publicaÃ§Ãµes e imagens associadas.',
                        ]
                    ) ?>
                <?php endif; ?>
                <?php if ($observation->publication?->publication_id !== null): ?>
                    <a class="btn btn-outline-brand" href="<?= Url::to(['publication/view', 'id' => $observation->publication->publication_id]) ?>">
                        <i class="fas fa-newspaper" aria-hidden="true"></i>
                        Abrir publicaÃ§Ã£o
                    </a>
                <?php elseif ($canCreatePublication && !$observation->needsManualReview() && $observation->observation_id !== null): ?>
                    <a class="btn btn-outline-brand" href="<?= Url::to(['publication/create', 'observationId' => $observation->observation_id]) ?>">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Criar publicaÃ§Ã£o
                    </a>
                <?php endif; ?>
            </div>
        </div>
            <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>ConfianÃ§a</span><strong><?= $observation->confidence !== null ? (int) round($observation->confidence * 100) . '%' : 'N/D' ?></strong></article>
            <article class="detail-stat-card"><span>Data</span><strong><?= Html::encode(Yii::$app->formatter->asDate($observation->observed_at, 'php:d/m/Y')) ?></strong></article>
            <article class="detail-stat-card"><span>Imagens</span><strong><?= count($imagePaths) ?></strong></article>
            <?php if ($observation->publication?->publication_id !== null): ?>
                <article class="detail-stat-card"><span>Publicado por</span><strong><?= Html::encode($observation->publication->user?->getFullName() ?? 'Sistema') ?></strong></article>
            <?php endif; ?>
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
                    <div class="detail-info-item"><span>EspÃ©cie</span><strong><?= Html::encode($observation->needsManualReview() ? 'A aguardar identificaÃ§Ã£o manual' : ($observation->getResolvedCommonName() ?: 'ObservaÃ§Ã£o botÃ¢nica')) ?></strong></div>
                    <?php if ($observation->needsManualReview()): ?>
                        <div class="detail-info-item"><span>PrediÃ§Ã£o original</span><strong><?= Html::encode($observation->predicted_scientific_name ?: 'NÃ£o conhecemos essa planta') ?></strong></div>
                    <?php endif; ?>
                        <div class="detail-info-item"><span>FamÃ­lia</span><strong><?= Html::encode($observation->getResolvedFamily() ?: 'N/D') ?></strong></div>
                    <div class="detail-info-item">
                        <span>LocalizaÃ§Ã£o</span>
                        <?php if ($observation->hasCoordinates()): ?>
                            <strong
                                id="observation-location-name"
                                data-latitude="<?= Html::encode((string) $observation->latitude) ?>"
                                data-longitude="<?= Html::encode((string) $observation->longitude) ?>"
                                data-fallback="<?= Html::encode($coordinateLabel) ?>"
                            >LocalizaÃ§Ã£o registada</strong>
                            <small><?= Html::encode($coordinateLabel) ?></small>
                        <?php else: ?>
                            <strong>Sem localizaÃ§Ã£o</strong>
                        <?php endif; ?>
                    </div>
                        <div class="detail-info-item"><span>Wikipedia</span><strong><?= $observation->enriched_wikipedia_url ? Html::a('Abrir referÃªncia', $observation->enriched_wikipedia_url, ['target' => '_blank', 'rel' => 'noopener']) : 'Sem referÃªncia' ?></strong></div>
                </div>
            </article>
            <article class="content-card content-card-soft detail-actions-card">
                <div class="detail-card-title">
                    <span class="detail-card-icon"><i class="fas fa-link" aria-hidden="true"></i></span>
                    <h2>LigaÃ§Ãµes</h2>
                </div>
                <div class="module-link-list detail-action-list">
                    <?php if (!empty($observation->plant_species_id)): ?><a href="<?= Url::to(['species/view', 'id' => $observation->plant_species_id]) ?>"><i class="fas fa-leaf" aria-hidden="true"></i><span>Abrir ficha da espÃ©cie</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                    <?php if ($observation->publication?->publication_id !== null): ?><a href="<?= Url::to(['publication/view', 'id' => $observation->publication->publication_id]) ?>"><i class="fas fa-newspaper" aria-hidden="true"></i><span>Abrir publicaÃ§Ã£o associada</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                    <?php if ($observation->hasCoordinates() && $observation->observation_id !== null): ?><a href="<?= Url::to(['map/index', 'observationId' => $observation->observation_id]) ?>"><i class="fas fa-map-location-dot" aria-hidden="true"></i><span>Ver no mapa</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                </div>
            </article>
        </div>
    </section>

    <section class="detail-section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Galeria</span>
                <h2>Imagens da observaÃ§Ã£o</h2>
            </div>
            <?php if (!empty($imagePaths)): ?>
                <span class="section-count"><?= count($imagePaths) ?> <?= count($imagePaths) === 1 ? 'imagem' : 'imagens' ?></span>
            <?php endif; ?>
        </div>
        <?php if (empty($imagePaths)): ?>
            <div class="empty-state-card">
                <h3>Sem imagem acessÃ­vel</h3>
                <p>Esta observaÃ§Ã£o nÃ£o tem ficheiros de imagem que a web consiga servir neste momento.</p>
            </div>
        <?php else: ?>
            <div class="observation-gallery-grid">
                <?php foreach ($imagePaths as $index => $path): ?>
                    <?php $imageUrl = Url::to(['media/upload-path', 'path' => $path]); ?>
                    <a class="observation-gallery-card" href="<?= $imageUrl ?>" target="_blank" rel="noopener">
                        <img src="<?= $imageUrl ?>" alt="Imagem da observaÃ§Ã£o <?= (int) $observation->observation_id ?>">
                        <span>Abrir imagem <?= $index + 1 ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
