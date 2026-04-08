<?php

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

$this->title = 'Especies';

$sortOptions = [
    'species' => 'Especie',
    'family' => 'Familia',
    'genus' => 'Genero',
];
?>
<div class="species-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Catalogo botanico</span>
            <h1 class="hero-title hero-title-tight">Especies observadas no ecossistema GeoDouro</h1>
            
        </div>
        <div class="hero-stack">
            <article class="hero-stat-card">
                <span>Especies</span>
                <strong><?= (int) $summary['speciesCount'] ?></strong>
            </article>
            <article class="hero-stat-card">
                <span>Observacoes</span>
                <strong><?= (int) $summary['observationsCount'] ?></strong>
            </article>
            <article class="hero-stat-card">
                <span>Familias</span>
                <strong><?= (int) $summary['familiesCount'] ?></strong>
            </article>
        </div>
    </section>

    <section class="catalog-toolbar mb-4">
        <form class="catalog-search" method="get" action="<?= Url::to(['species/index']) ?>">
            <label class="search-field">
                <span class="search-icon">&#9906;</span>
                <input
                    type="search"
                    name="q"
                    value="<?= Html::encode($queryText) ?>"
                    placeholder="Pesquisar por especie, nome comum, familia ou genero"
                >
            </label>
            <input type="hidden" name="sort" value="<?= Html::encode($sort) ?>">
            <button type="submit" class="btn btn-brand">Pesquisar</button>
        </form>

        <div class="filter-row">
            <?php foreach ($sortOptions as $value => $label): ?>
                <?php $isActive = $sort === $value; ?>
                <a
                    class="filter-chip<?= $isActive ? ' is-active' : '' ?>"
                    href="<?= Url::to(['species/index', 'sort' => $value, 'q' => $queryText ?: null]) ?>"
                >
                    <?= Html::encode($label) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (empty($species)): ?>
        <section class="empty-state-card">
            <h2>Nenhuma especie encontrada</h2>
            <p>
                Ajusta a pesquisa ou limpa os filtros para voltar a ver o catalogo completo.
            </p>
        </section>
    <?php else: ?>
        <section class="species-grid">
            <?php foreach ($species as $item): ?>
                <article class="species-card-web">
                    <a class="species-card-link" href="<?= Url::to(['species/view', 'id' => $item->plant_species_id]) ?>">
                        <div class="species-card-media">
                            <div class="species-orb"></div>
                            <div class="species-media-copy">
                                <span class="species-media-label"><?= Html::encode(mb_strtoupper($item->genus)) ?></span>
                                <strong><?= Html::encode($item->species) ?></strong>
                            </div>
                        </div>
                        <div class="species-card-body">
                            <p class="species-scientific-name"><?= Html::encode($item->scientific_name) ?></p>
                            <h2><?= Html::encode($item->common_name ?: 'Sem nome comum registado') ?></h2>
                            <div class="species-meta-row">
                                <span class="species-meta-chip"><?= Html::encode($item->family) ?></span>
                                <span class="species-meta-chip"><?= Html::encode($item->genus) ?></span>
                            </div>
                            <div class="species-card-footer">
                                <span><?= (int) $item->image_count ?> imagens de referencia</span>
                                <span class="species-card-cta">Abrir detalhe</span>
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
