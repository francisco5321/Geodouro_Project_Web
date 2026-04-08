<?php

use app\models\Publication;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var Publication[] $publications */
/** @var yii\data\Pagination $pagination */
/** @var array $summary */
/** @var string $scope */

$this->title = 'Publicacoes';
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Modulo editorial</span>
            <h1 class="hero-title hero-title-tight">Publicacoes geridas por autores e administradores</h1>
            <p class="hero-text">Cada utilizador autenticado pode trabalhar as suas publicacoes e o admin ganha controlo editorial total sobre o catalogo.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Total</span><strong><?= (int) $summary['total'] ?></strong></article>
            <article class="detail-stat-card"><span>Rascunhos</span><strong><?= (int) $summary['drafts'] ?></strong></article>
            <article class="detail-stat-card"><span>Publicadas</span><strong><?= (int) $summary['published'] ?></strong></article>
            <article class="detail-stat-card"><span>Observacoes prontas</span><strong><?= (int) $summary['availableObservationCount'] ?></strong></article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <section class="toolbar-card mb-4">
        <div class="toolbar-row">
            <div class="segmented-links">
                <a class="<?= $scope === 'all' ? 'is-active' : '' ?>" href="<?= Url::to(['publication/index', 'scope' => 'all']) ?>">Todas</a>
                <a class="<?= $scope === 'mine' ? 'is-active' : '' ?>" href="<?= Url::to(['publication/index', 'scope' => 'mine']) ?>">Minhas</a>
            </div>
            <div class="toolbar-actions">
                <a class="btn btn-outline-brand" href="<?= Url::to(['visit/index']) ?>">Quero visitar</a>
                <?php if ($summary['availableObservationCount'] > 0): ?>
                    <a class="btn btn-brand" href="<?= Url::to(['publication/create']) ?>">Nova publicacao</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="publication-grid">
        <?php foreach ($publications as $publication): ?>
            <article class="publication-card publication-card-rich">
                <?php if ($publication->getCoverImagePath() !== null): ?>
                    <a class="publication-cover" href="<?= Url::to(['publication/view', 'id' => $publication->publication_id]) ?>">
                        <img src="<?= Url::to(['media/publication-image', 'id' => $publication->publication_id, 'index' => 0]) ?>" alt="Capa da publicacao <?= (int) $publication->publication_id ?>">
                    </a>
                <?php endif; ?>
                <div class="publication-card-body">
                    <div class="card-chip-row mb-2">
                        <span class="species-meta-chip<?= $publication->isPublished() ? ' chip-highlight' : '' ?>"><?= Html::encode($publication->getStatusLabel()) ?></span>
                        <span class="species-meta-chip"><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></span>
                    </div>
                    <p class="species-scientific-name"><?= Html::encode($publication->plantSpecies?->scientific_name ?? $publication->observation?->getResolvedScientificName() ?? 'Sem especie associada') ?></p>
                    <h2><?= Html::encode($publication->title ?: 'Publicacao botanica') ?></h2>
                    <p class="publication-copy"><?= Html::encode($publication->description ?: 'Sem descricao editorial registada para esta publicacao.') ?></p>
                    <div class="species-meta-row">
                        <span class="species-meta-chip"><?= Html::encode(Yii::$app->formatter->asDate($publication->published_at, 'php:d/m/Y')) ?></span>
                        <span class="species-meta-chip"><?= count($publication->publicationImages) ?> imagens</span>
                    </div>
                    <div class="timeline-card-actions">
                        <a href="<?= Url::to(['publication/view', 'id' => $publication->publication_id]) ?>">Abrir</a>
                        <?php if ($publication->canBeManagedBy(Yii::$app->user->identity)): ?>
                            <a href="<?= Url::to(['publication/update', 'id' => $publication->publication_id]) ?>">Editar</a>
                        <?php endif; ?>
                        <?php if ($publication->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $publication->plant_species_id]) ?>">Especie</a><?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

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
        ]) ?>
    </div>
</div>
