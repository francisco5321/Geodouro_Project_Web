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
                <span class="species-meta-chip<?= $publication->isPublished() ? ' chip-highlight' : '' ?>"><?= Html::encode($publication->getStatusLabel()) ?></span>
                <span class="species-meta-chip"><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></span>
                <span class="species-meta-chip"><?= count($imagePaths) ?> imagens</span>
            </div>
            <p class="hero-text"><?= Html::encode($publication->description ?: 'Sem texto editorial associado a esta publicacao.') ?></p>
            <div class="hero-cta-row mt-4">
                <?php if ($publication->canBeManagedBy(Yii::$app->user->identity)): ?>
                    <a class="btn btn-brand" href="<?= Url::to(['publication/update', 'id' => $publication->publication_id]) ?>">Editar publicacao</a>
                    <?php if (!$publication->isPublished()): ?>
                        <?= Html::beginForm(['publication/publish', 'id' => $publication->publication_id], 'post', ['class' => 'd-inline-block']) ?>
                            <?= Html::submitButton('Publicar agora', ['class' => 'btn btn-outline-brand']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                <?php endif; ?>
                <?= Html::beginForm(['visit/toggle-publication', 'id' => $publication->publication_id], 'post', ['class' => 'd-inline-block']) ?>
                    <?= Html::submitButton($publication->isSavedForUser(Yii::$app->user->identity) ? 'Remover de Quero visitar' : 'Guardar em Quero visitar', ['class' => 'btn btn-outline-brand']) ?>
                <?= Html::endForm() ?>
            </div>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

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
                    <a href="<?= Url::to(['visit/index']) ?>">Abrir Quero visitar</a>
                </div>
            </article>
        </div>
    </section>

    <?php if ($publication->canBeManagedBy(Yii::$app->user->identity)): ?>
        <section class="detail-section">
            <div class="content-card danger-zone-card">
                <h2>Gestao administrativa</h2>
                <p>Podes continuar a editar esta publicacao ou removê-la por completo do portal.</p>
                <?= Html::beginForm(['publication/delete', 'id' => $publication->publication_id], 'post') ?>
                    <?= Html::submitButton('Eliminar publicacao', ['class' => 'btn btn-outline-danger', 'data-confirm' => 'Queres mesmo eliminar esta publicacao?']) ?>
                <?= Html::endForm() ?>
            </div>
        </section>
    <?php endif; ?>

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
