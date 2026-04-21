<?php

use app\components\StatCard;
use app\models\Publication;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var Publication[] $publications */
/** @var yii\data\Pagination $pagination */
/** @var array $summary */
/** @var string $scope */

$this->title = 'Publicações';
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-newspaper" aria-hidden="true"></i>
                Módulo Editorial
            </span>
            <h1 class="hero-title hero-title-tight">Publicações geridas por autores e administradores</h1>
            <p class="hero-text">Cada utilizador autenticado pode gerir as suas publicações e o administrador ganha controlo editorial total sobre o catálogo.</p>
        </div>
        <div class="detail-stat-grid">
            <?= StatCard::widget([
                'label' => 'Total',
                'value' => (int) $summary['total'],
                'icon' => 'fas fa-chart-line',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Rascunhos',
                'value' => (int) $summary['drafts'],
                'icon' => 'fas fa-file-alt',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Publicadas',
                'value' => (int) $summary['published'],
                'icon' => 'fas fa-star',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Observações Prontas',
                'value' => (int) $summary['availableObservationCount'],
                'icon' => 'fas fa-check',
            ]) ?>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert-success-custom mb-4">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <section class="catalog-toolbar mb-4">
        <div class="toolbar-header">
            <h2 class="section-title">
                <i class="fas fa-filter" aria-hidden="true"></i>
                Filtrar Publicações
            </h2>
        </div>
        <div class="toolbar-row">
            <div class="filter-row">
                <a class="filter-chip<?= $scope === 'all' ? ' is-active' : '' ?>" href="<?= Url::to(['publication/index', 'scope' => 'all']) ?>">
                    <i class="fas fa-list" aria-hidden="true"></i>
                    Todas
                </a>
                <a class="filter-chip<?= $scope === 'mine' ? ' is-active' : '' ?>" href="<?= Url::to(Yii::$app->user->isGuest ? ['site/login'] : ['publication/index', 'scope' => 'mine']) ?>">
                    <i class="fas fa-user-edit" aria-hidden="true"></i>
                    Minhas publicações
                </a>
            </div>
            <div class="toolbar-actions">
                <a class="btn btn-outline" href="<?= Url::to(['visit/index']) ?>">
                    <i class="fas fa-heart" aria-hidden="true"></i>
                    Quero visitar
                </a>
            </div>
        </div>
    </section>

    <section class="publication-grid">
        <?php foreach ($publications as $publication): ?>
            <article class="publication-card publication-card-rich">
                <?php if ($publication->getCoverImagePath() !== null): ?>
                    <a class="publication-cover" href="<?= Url::to(['publication/view', 'id' => $publication->publication_id]) ?>">
                        <img src="<?= Url::to(['media/publication-image', 'id' => $publication->publication_id, 'index' => 0]) ?>" alt="Capa da publicação <?= (int) $publication->publication_id ?>">
                    </a>
                <?php endif; ?>
                <div class="publication-card-body">
                    <div class="card-chip-row mb-2">
                        <span class="species-meta-chip<?= $publication->isPublished() ? ' chip-highlight' : '' ?>"><?= Html::encode($publication->getStatusLabel()) ?></span>
                        <span class="species-meta-chip"><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></span>
                    </div>
                    <p class="species-scientific-name"><?= Html::encode($publication->plantSpecies?->scientific_name ?? $publication->observation?->getResolvedScientificName() ?? 'Sem espécie associada') ?></p>
                    <h2><?= Html::encode($publication->title ?: 'Publicação botânica') ?></h2>
                    <p class="publication-copy"><?= Html::encode($publication->description ?: 'Sem descrição editorial registada para esta publicação.') ?></p>
                    <div class="species-meta-row">
                        <span class="species-meta-chip"><?= Html::encode(Yii::$app->formatter->asDate($publication->published_at, 'php:d/m/Y')) ?></span>
                        <span class="species-meta-chip"><?= count($publication->publicationImages) ?> imagens</span>
                    </div>
                    <div class="timeline-card-actions">
                        <a href="<?= Url::to(['publication/view', 'id' => $publication->publication_id]) ?>">Abrir</a>
                        <?php if ($publication->canBeManagedBy(Yii::$app->user->identity)): ?>
                            <a href="<?= Url::to(['publication/update', 'id' => $publication->publication_id]) ?>">Editar</a>
                        <?php endif; ?>
                        <?php if ($publication->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $publication->plant_species_id]) ?>">Espécie</a><?php endif; ?>
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
