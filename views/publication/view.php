<?php

use app\models\Publication;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Publication $publication */

$this->title = $publication->title ?: 'Publicação #' . $publication->publication_id;
$imagePaths = $publication->getImageGalleryPaths();
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
            <p class="species-detail-scientific"><?= Html::encode($publication->plantSpecies?->scientific_name ?? $publication->observation?->getResolvedScientificName() ?? 'Sem espécie associada') ?></p>
            <div class="species-meta-row">
                <span class="species-meta-chip<?= $publication->isPublished() ? ' chip-highlight' : '' ?>"><?= Html::encode($publication->getStatusLabel()) ?></span>
                <span class="species-meta-chip"><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></span>
                <span class="species-meta-chip"><?= count($imagePaths) ?> imagens</span>
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
                <?php if (Yii::$app->user->isGuest): ?>
                    <a class="btn btn-outline-brand" href="<?= Url::to(['site/login']) ?>">Entrar para guardar em Quero visitar</a>
                <?php else: ?>
                    <?= Html::beginForm(['visit/toggle-publication', 'id' => $publication->publication_id], 'post', ['class' => 'd-inline-block']) ?>
                        <?= Html::submitButton($publication->isSavedForUser(Yii::$app->user->identity) ? 'Remover de Quero visitar' : 'Guardar em Quero visitar', ['class' => 'btn btn-outline-brand']) ?>
                    <?= Html::endForm() ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <section class="detail-section">
        <div class="detail-split-grid detail-context-grid">
            <article class="content-card detail-context-card">
                <div class="detail-card-title">
                    <span class="detail-card-icon"><i class="fas fa-circle-info" aria-hidden="true"></i></span>
                    <h2>Contexto editorial</h2>
                </div>
                <div class="info-list detail-info-list">
                    <div class="detail-info-item"><span>ID</span><strong>#<?= (int) $publication->publication_id ?></strong></div>
                    <div class="detail-info-item"><span>Observação</span><strong>#<?= (int) $publication->observation_id ?></strong></div>
                    <div class="detail-info-item"><span>Autor</span><strong><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></strong></div>
                    <div class="detail-info-item"><span>Espécie</span><strong><?= Html::encode($publication->plantSpecies?->common_name ?: ($publication->observation?->getResolvedCommonName() ?: 'N/D')) ?></strong></div>
                </div>
            </article>
            <article class="content-card content-card-soft detail-actions-card">
                <div class="detail-card-title">
                    <span class="detail-card-icon"><i class="fas fa-link" aria-hidden="true"></i></span>
                    <h2>Ligações</h2>
                </div>
                <div class="module-link-list detail-action-list">
                    <a href="<?= Url::to(['observation/view', 'id' => $publication->observation_id]) ?>"><i class="fas fa-binoculars" aria-hidden="true"></i><span>Abrir observação original</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    <?php if ($publication->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $publication->plant_species_id]) ?>"><i class="fas fa-leaf" aria-hidden="true"></i><span>Abrir ficha da espécie</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><?php endif; ?>
                    <a href="<?= Url::to(['map/index']) ?>"><i class="fas fa-map-location-dot" aria-hidden="true"></i><span>Ver observações no mapa</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    <a href="<?= Url::to(['visit/index']) ?>"><i class="fas fa-route" aria-hidden="true"></i><span>Abrir Quero visitar</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a>
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
                <h3>Sem galeria acessivel</h3>
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
