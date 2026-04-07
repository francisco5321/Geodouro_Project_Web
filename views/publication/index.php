<?php

use app\models\Publication;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var Publication[] $publications */
/** @var yii\data\Pagination $pagination */
/** @var array $summary */

$this->title = 'Publicacoes';
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Modulo editorial</span>
            <h1 class="hero-title hero-title-tight">Publicacoes geradas a partir das observacoes confirmadas</h1>
            <p class="hero-text">Esta camada aproxima a web do papel editorial e de monitorizacao publica do projeto, mantendo a mesma identidade naturalista do mobile.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Total</span><strong><?= (int) $summary['total'] ?></strong></article>
            <article class="detail-stat-card"><span>Especies</span><strong><?= (int) $summary['species'] ?></strong></article>
            <article class="detail-stat-card"><span>Autores</span><strong><?= (int) $summary['authors'] ?></strong></article>
            <article class="detail-stat-card"><span>Estado</span><strong>Ativo</strong></article>
        </div>
    </section>

    <section class="publication-grid">
        <?php foreach ($publications as $publication): ?>
            <article class="publication-card">
                <p class="species-scientific-name"><?= Html::encode($publication->plantSpecies?->scientific_name ?? $publication->observation?->getResolvedScientificName() ?? 'Sem especie associada') ?></p>
                <h2><?= Html::encode($publication->title ?: 'Publicacao botanica') ?></h2>
                <p class="publication-copy"><?= Html::encode($publication->description ?: 'Sem descricao editorial registada para esta publicacao.') ?></p>
                <div class="species-meta-row">
                    <span class="species-meta-chip"><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></span>
                    <span class="species-meta-chip"><?= Html::encode(Yii::$app->formatter->asDate($publication->published_at, 'php:d/m/Y')) ?></span>
                </div>
                <div class="timeline-card-actions">
                    <a href="<?= Url::to(['publication/view', 'id' => $publication->publication_id]) ?>">Abrir publicacao</a>
                    <?php if ($publication->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $publication->plant_species_id]) ?>">Abrir especie</a><?php endif; ?>
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
