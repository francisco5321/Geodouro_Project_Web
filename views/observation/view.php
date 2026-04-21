<?php

use app\models\Observation;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Observation $observation */

$this->title = 'Observacao #' . $observation->observation_id;
$statusLabel = $observation->is_published ? 'Publicada' : ($observation->sync_status === Observation::SYNC_SYNCED ? 'Sincronizada' : ($observation->sync_status === Observation::SYNC_FAILED ? 'Falha de sincronizacao' : 'Pendente'));
$imagePaths = $observation->getImageGalleryPaths();
$canCreatePublication = Yii::$app->user->identity?->isAdmin() || (int) $observation->user_id === (int) Yii::$app->user->id;
?>
<div class="module-shell">
    <a class="back-link" href="<?= Url::to(['observation/index']) ?>">&larr; Voltar as observações</a>

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
                <?php if ($observation->publication !== null): ?>
                    <a class="btn btn-brand" href="<?= Url::to(['publication/view', 'id' => $observation->publication->publication_id]) ?>">Abrir publicação</a>
                <?php elseif ($canCreatePublication): ?>
                    <a class="btn btn-brand" href="<?= Url::to(['publication/create', 'observationId' => $observation->observation_id]) ?>">Criar publicação</a>
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
                    <div class="detail-info-item"><span>Especie</span><strong><?= Html::encode($observation->getResolvedCommonName() ?: 'Observacao botanica') ?></strong></div>
                    <div class="detail-info-item"><span>Familia</span><strong><?= Html::encode($observation->getResolvedFamily() ?: 'N/D') ?></strong></div>
                    <div class="detail-info-item"><span>Coordenadas</span><strong><?= $observation->hasCoordinates() ? Html::encode(number_format((float) $observation->latitude, 5) . ', ' . number_format((float) $observation->longitude, 5)) : 'Sem localizacao' ?></strong></div>
                    <div class="detail-info-item"><span>Wikipedia</span><strong><?= $observation->enriched_wikipedia_url ? Html::a('Abrir referencia', $observation->enriched_wikipedia_url, ['target' => '_blank', 'rel' => 'noopener']) : 'Sem referencia' ?></strong></div>
                </div>
            </article>
            <article class="content-card content-card-soft detail-actions-card">
                <div class="detail-card-title">
                    <span class="detail-card-icon"><i class="fas fa-link" aria-hidden="true"></i></span>
                    <h2>Ligações</h2>
                </div>
                <div class="module-link-list detail-action-list">
                    <?php if ($observation->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $observation->plant_species_id]) ?>"><i class="fas fa-leaf" aria-hidden="true"></i><span>Abrir ficha da especie</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
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
        </div>
        <?php if (empty($imagePaths)): ?>
            <div class="empty-state-card">
                <h3>Sem imagem acessível</h3>
                <p>Esta observação nao tem ficheiros de imagem que a web consiga servir neste momento.</p>
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
