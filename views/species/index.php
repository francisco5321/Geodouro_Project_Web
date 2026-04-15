<?php

use app\components\StatCard;
use app\components\FilterChips;
use app\components\EmptyState;
use app\models\PlantSpecies;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var PlantSpecies[] $species */
/** @var yii\data\Pagination $pagination */
/** @var string $queryText */
/** @var string $sort */
/** @var array $summary */

$this->title = 'Espécies';

$sortOptions = [
    ['label' => 'Espécie', 'value' => 'species', 'icon' => 'fas fa-sort-alpha-down'],
    ['label' => 'Família', 'value' => 'family', 'icon' => 'fas fa-sitemap'],
    ['label' => 'Gênero', 'value' => 'genus', 'icon' => 'fas fa-layer-group'],
];
?>
<div class="species-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-leaf" aria-hidden="true"></i>
                Catálogo Botânico
            </span>
            <h1 class="hero-title hero-title-tight">Espécies observadas no ecossistema GeoDouro</h1>
            <p class="hero-text">Explora o catálogo completo de espécies, organisadas por família e género, com informações detalhadas e imagens.</p>
        </div>
        <div class="detail-stat-grid">
            <?= StatCard::widget([
                'label' => 'Espécies',
                'value' => (int) $summary['speciesCount'],
                'icon' => 'fas fa-leaf',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Observações',
                'value' => (int) $summary['observationsCount'],
                'icon' => 'fas fa-camera',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Famílias',
                'value' => (int) $summary['familiesCount'],
                'icon' => 'fas fa-sitemap',
            ]) ?>
        </div>
    </section>

    <section class="catalog-toolbar mb-4">
        <div class="toolbar-header">
            <h2 class="section-title">
                <i class="fas fa-search" aria-hidden="true"></i>
                Pesquisar e Filtrar
            </h2>
        </div>
        <form class="catalog-search" method="get" action="<?= Url::to(['species/index']) ?>" role="search">
            <label class="search-field">
                <span class="search-icon" aria-hidden="true">
                    <i class="fas fa-search"></i>
                </span>
                <input
                    type="search"
                    name="q"
                    value="<?= Html::encode($queryText) ?>"
                    placeholder="Pesquisar por espécie, nome comum, família ou gênero"
                    aria-label="Pesquisar espécies"
                >
            </label>
            <input type="hidden" name="sort" value="<?= Html::encode($sort) ?>">
            <button type="submit" class="btn btn-brand">
                <i class="fas fa-search" aria-hidden="true"></i>
                Pesquisar
            </button>
        </form>

        <div class="filter-row">
            <?php foreach ($sortOptions as $option): ?>
                <?php $isActive = $sort === $option['value']; ?>
                <a
                    class="filter-chip<?= $isActive ? ' is-active' : '' ?>"
                    href="<?= Url::to(['species/index', 'sort' => $option['value'], 'q' => $queryText ?: null]) ?>"
                    title="Ordenar por <?= Html::encode($option['label']) ?>"
                    role="button"
                    tabindex="0"
                >
                    <i class="<?= $option['icon'] ?>" aria-hidden="true"></i>
                    <?= Html::encode($option['label']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (empty($species)): ?>
        <?= EmptyState::widget([
            'icon' => 'fas fa-search',
            'title' => 'Nenhuma espécie encontrada',
            'message' => 'Ajusta a pesquisa ou limpa os filtros para voltar a ver o catálogo completo.',
            'actions' => [
                ['label' => 'Voltar ao início', 'url' => ['species/index'], 'icon' => 'fas fa-redo', 'class' => 'btn-outline-brand'],
            ],
        ]) ?>
    <?php else: ?>
        <section class="species-grid">
            <?php foreach ($species as $item): ?>
                <article class="species-card-web">
                    <a class="species-card-link" href="<?= Url::to(['species/view', 'id' => $item->plant_species_id]) ?>" title="Ver detalhes de <?= Html::encode($item->common_name ?: $item->scientific_name) ?>">
                        <div class="species-card-media">
                            <div class="species-orb"></div>
                            <div class="species-media-copy">
                                <span class="species-media-label"><?= Html::encode(mb_strtoupper($item->genus)) ?></span>
                                <strong><?= Html::encode($item->species) ?></strong>
                            </div>
                        </div>
                        <div class="species-card-body">
                            <p class="species-scientific-name" lang="la"><?= Html::encode($item->scientific_name) ?></p>
                            <h2><?= Html::encode($item->common_name ?: 'Sem nome comum registado') ?></h2>
                            <div class="species-meta-row">
                                <span class="species-meta-chip" title="Família">
                                    <i class="fas fa-sitemap" aria-hidden="true"></i>
                                    <?= Html::encode($item->family) ?>
                                </span>
                                <span class="species-meta-chip" title="Gênero">
                                    <i class="fas fa-layer-group" aria-hidden="true"></i>
                                    <?= Html::encode($item->genus) ?>
                                </span>
                            </div>
                            <div class="species-card-footer">
                                <span class="icon-text">
                                    <i class="fas fa-image" aria-hidden="true"></i>
                                    <?= (int) $item->image_count ?> imagens
                                </span>
                                <span class="species-card-cta">
                                    Abrir detalhe
                                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </span>
                            </div>
                        </div>
                    </a>
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
    <?php endif; ?>
</div>
