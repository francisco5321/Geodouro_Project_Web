<?php

use app\models\Observation;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var Observation[] $observations */
/** @var yii\data\Pagination $pagination */
/** @var string $status */
/** @var array $summary */

$this->title = 'Observacoes';

$filters = [
    'all' => 'Todas',
    Observation::SYNC_PENDING => 'Pendentes',
    Observation::SYNC_SYNCED => 'Sincronizadas',
    Observation::SYNC_FAILED => 'Falhadas',
    'PUBLISHED' => 'Publicadas',
];
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Modulo de observacoes</span>
            <h1 class="hero-title hero-title-tight">Registos captados e sincronizados a partir da app mobile</h1>
            <p class="hero-text">A web passa a dar visibilidade aos estados de sincronizacao, classificacao e publicacao que ja existem no ecossistema GeoDouro.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Total</span><strong><?= (int) $summary['total'] ?></strong></article>
            <article class="detail-stat-card"><span>Publicadas</span><strong><?= (int) $summary['published'] ?></strong></article>
            <article class="detail-stat-card"><span>Pendentes</span><strong><?= (int) $summary['pending'] ?></strong></article>
            <article class="detail-stat-card"><span>Falhadas</span><strong><?= (int) $summary['failed'] ?></strong></article>
        </div>
    </section>

    <section class="catalog-toolbar mb-4">
        <div class="filter-row">
            <?php foreach ($filters as $value => $label): ?>
                <a class="filter-chip<?= $status === $value ? ' is-active' : '' ?>" href="<?= Url::to(['observation/index', 'status' => $value === 'all' ? null : $value]) ?>"><?= Html::encode($label) ?></a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="timeline-grid">
        <?php foreach ($observations as $observation): ?>
            <?php $statusLabel = $observation->is_published ? 'Publicada' : ($observation->sync_status === Observation::SYNC_SYNCED ? 'Sincronizada' : ($observation->sync_status === Observation::SYNC_FAILED ? 'Falha de sincronizacao' : 'Pendente')); ?>
            <article class="timeline-card">
                <div class="timeline-card-top">
                    <div>
                        <p class="species-scientific-name"><?= Html::encode($observation->getResolvedScientificName() ?: 'Sem classificacao') ?></p>
                        <h2><?= Html::encode($observation->getResolvedCommonName() ?: 'Observacao botanica') ?></h2>
                    </div>
                    <span class="status-pill<?= $observation->is_published ? ' is-published' : '' ?>"><?= Html::encode($statusLabel) ?></span>
                </div>
                <div class="timeline-card-grid">
                    <div><span>Autor</span><strong><?= Html::encode($observation->user?->getFullName() ?? 'Sistema') ?></strong></div>
                    <div><span>Data</span><strong><?= Html::encode(Yii::$app->formatter->asDatetime($observation->observed_at, 'php:d/m/Y H:i')) ?></strong></div>
                    <div><span>Confianca</span><strong><?= $observation->confidence !== null ? (int) round($observation->confidence * 100) . '%' : 'N/D' ?></strong></div>
                    <div><span>Especie</span><strong><?= Html::encode($observation->plantSpecies?->scientific_name ?? 'Nao associada') ?></strong></div>
                </div>
                <div class="timeline-card-actions">
                    <?php if ($observation->plant_species_id): ?>
                        <a href="<?= Url::to(['species/view', 'id' => $observation->plant_species_id]) ?>">Abrir especie</a>
                    <?php endif; ?>
                    <a href="<?= Url::to(['observation/view', 'id' => $observation->observation_id]) ?>">Ver observacao</a>
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
