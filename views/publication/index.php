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
    <section class="species-hero publication-hero mb-4">
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
                <a class="btn <?= $scope === 'all' ? 'btn-brand' : 'btn-outline' ?>" href="<?= Url::to(['publication/index', 'scope' => 'all']) ?>">
                    <i class="fas fa-list" aria-hidden="true"></i>
                    Todas
                </a>
                <a class="btn <?= $scope === 'mine' ? 'btn-brand' : 'btn-outline' ?>" href="<?= Url::to(Yii::$app->user->isGuest ? ['site/login'] : ['publication/index', 'scope' => 'mine']) ?>">
                    <i class="fas fa-user-edit" aria-hidden="true"></i>
                    Minhas publicações
                </a>
            </div>
        </div>
    </section>

    <?php if (empty($publications) && $scope === 'mine'): ?>
        <section class="empty-state-card">
            <h2>Sem publicações tuas</h2>
            <p>Cria uma publicação para a poderes ver aqui.</p>
        </section>
    <?php else: ?>
    <section class="publication-grid">
        <?php foreach ($publications as $publication): ?>
            <?php
                $publicationUrl = Url::to(['publication/view', 'id' => $publication->publication_id]);
                $speciesName = $publication->plantSpecies?->scientific_name ?? $publication->observation?->getResolvedScientificName() ?? 'Sem espécie associada';
                $authorName = $publication->user?->getFullName() ?? 'Sistema';
                $hasCover = $publication->getCoverImagePath() !== null;
                $imageCount = count($publication->publicationImages);
            ?>
            <article class="publication-card publication-card-rich">
                <a class="publication-card-link" href="<?= $publicationUrl ?>" title="Abrir <?= Html::encode($publication->title ?: 'publicação botânica') ?>">
                    <div class="publication-card-media<?= $hasCover ? ' has-photo' : '' ?>">
                        <?php if ($hasCover): ?>
                            <img
                                class="publication-cover-photo"
                                src="<?= Url::to(['media/upload-path', 'path' => $publication->getCoverImagePath()]) ?>"
                                alt="Capa da publicação <?= (int) $publication->publication_id ?>"
                                loading="lazy"
                            >
                        <?php endif; ?>
                        <div class="species-orb"></div>
                        <div class="publication-media-copy">
                            <span class="publication-media-label"><?= Html::encode($publication->getStatusLabel()) ?></span>
                            <strong><?= Html::encode($authorName) ?></strong>
                        </div>
                    </div>
                </a>

                <div class="publication-card-body">
                    <p class="species-scientific-name"><?= Html::encode($speciesName) ?></p>
                    <h2><a href="<?= $publicationUrl ?>"><?= Html::encode($publication->title ?: 'Publicação botânica') ?></a></h2>
                    <p class="publication-copy"><?= Html::encode($publication->description ?: 'Sem descrição editorial registada para esta publicação.') ?></p>
                    <div class="species-meta-row">
                        <span class="species-meta-chip" title="Data">
                            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                            <?= Html::encode(Yii::$app->formatter->asDate($publication->published_at, 'php:d/m/Y')) ?>
                        </span>
                        <span class="species-meta-chip" title="Imagens">
                            <i class="fas fa-image" aria-hidden="true"></i>
                            <?= $imageCount ?> <?= $imageCount === 1 ? 'imagem' : 'imagens' ?>
                        </span>
                    </div>
                    <div class="publication-card-footer">
                        <?php if ($publication->canBeManagedBy(Yii::$app->user->identity)): ?>
                            <a href="<?= Url::to(['publication/update', 'id' => $publication->publication_id]) ?>">Editar</a>
                        <?php endif; ?>
                        <?php if ($publication->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $publication->plant_species_id]) ?>">Espécie</a><?php endif; ?>
                        <a class="species-card-cta" href="<?= $publicationUrl ?>">
                            Abrir detalhe
                            <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

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
</div>
