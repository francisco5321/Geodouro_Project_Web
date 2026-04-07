<?php

use app\models\Publication;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Publication $publication */

$this->title = $publication->title ?: 'Publicacao #' . $publication->publication_id;
$imagePaths = $publication->getImageGalleryPaths();
?>
<div class="module-shell">
    <a class="back-link" href="<?= Url::to(['publication/index']) ?>">&larr; Voltar as publicacoes</a>

    <section class="publication-hero mb-4">
        <div class="publication-hero-media">
            <?php if (!empty($imagePaths)): ?>
                <img src="<?= Url::to(['media/publication-image', 'id' => $publication->publication_id, 'index' => 0]) ?>" alt="Capa da publicacao <?= (int) $publication->publication_id ?>">
            <?php else: ?>
                <div class="publication-hero-placeholder">
                    <span class="eyebrow">Sem capa</span>
                    <strong>Conteudo editorial</strong>
                </div>
            <?php endif; ?>
        </div>
        <div class="species-detail-copy">
            <span class="eyebrow">Publicacao</span>
            <h1 class="hero-title hero-title-tight"><?= Html::encode($publication->title ?: 'Publicacao botanica') ?></h1>
            <p class="species-detail-scientific"><?= Html::encode($publication->plantSpecies?->scientific_name ?? $publication->observation?->getResolvedScientificName() ?? 'Sem especie associada') ?></p>
            <div class="species-meta-row">
                <span class="species-meta-chip"><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></span>
                <span class="species-meta-chip"><?= Html::encode(Yii::$app->formatter->asDatetime($publication->published_at, 'php:d/m/Y H:i')) ?></span>
                <span class="species-meta-chip"><?= count($imagePaths) ?> imagens</span>
            </div>
            <p class="hero-text"><?= Html::encode($publication->description ?: 'Sem texto editorial associado a esta publicacao.') ?></p>
        </div>
    </section>

    <section class="detail-section">
        <div class="detail-split-grid">
            <article class="content-card">
                <h2>Contexto editorial</h2>
                <div class="info-list">
                    <div><span>ID</span><strong>#<?= (int) $publication->publication_id ?></strong></div>
                    <div><span>Observacao</span><strong>#<?= (int) $publication->observation_id ?></strong></div>
                    <div><span>Autor</span><strong><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></strong></div>
                    <div><span>Especie</span><strong><?= Html::encode($publication->plantSpecies?->common_name ?: ($publication->observation?->getResolvedCommonName() ?: 'N/D')) ?></strong></div>
                </div>
            </article>
            <article class="content-card content-card-soft">
                <h2>Ligacoes</h2>
                <div class="module-link-list">
                    <a href="<?= Url::to(['observation/view', 'id' => $publication->observation_id]) ?>">Abrir observacao original</a>
                    <?php if ($publication->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $publication->plant_species_id]) ?>">Abrir ficha da especie</a><?php endif; ?>
                    <a href="<?= Url::to(['map/index']) ?>">Ver observacoes no mapa</a>
                </div>
            </article>
        </div>
    </section>

    <section class="detail-section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Galeria editorial</span>
                <h2>Imagens da publicacao</h2>
            </div>
        </div>
        <?php if (empty($imagePaths)): ?>
            <div class="empty-state-card">
                <h3>Sem galeria acessivel</h3>
                <p>Esta publicacao ainda nao tem imagens acessiveis pela web.</p>
            </div>
        <?php else: ?>
            <div class="observation-gallery-grid">
                <?php foreach ($imagePaths as $index => $path): ?>
                    <a class="observation-gallery-card" href="<?= Url::to(['media/publication-image', 'id' => $publication->publication_id, 'index' => $index]) ?>" target="_blank" rel="noopener">
                        <img src="<?= Url::to(['media/publication-image', 'id' => $publication->publication_id, 'index' => $index]) ?>" alt="Imagem da publicacao <?= (int) $publication->publication_id ?>">
                        <span>Abrir imagem <?= $index + 1 ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
