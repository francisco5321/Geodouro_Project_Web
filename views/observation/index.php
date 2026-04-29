<?php

use app\components\EmptyState;
use app\components\StatCard;
use app\models\Observation;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var Observation[] $observations */
/** @var yii\data\Pagination $pagination */
/** @var string $queryText */
/** @var string $status */
/** @var bool $myObservationsOnly */
/** @var array $summary */

$this->title = 'ObservaÃ§Ãµes';
$isAdmin = Yii::$app->user->identity?->isAdmin() ?? false;
?>
<div class="module-shell">
    <section class="species-hero observation-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-binoculars" aria-hidden="true"></i>
                Ã³dulo de observaÃ§Ãµes
            </span>
            <h1 class="hero-title hero-title-tight">Registos captados e sincronizados a partir da app mobile</h1>
            <p class="hero-text">Visualiza, filtra e gere todas as observaÃ§Ãµes botÃ¢nicas recolhidas pelo projeto GeoFlora.</p>
        </div>
        <div class="detail-stat-grid">
            <?php if ($isAdmin): ?>
                <?= StatCard::widget([
                    'label' => 'RevisÃ£o manual',
                    'value' => (int) ($summary['manualReview'] ?? 0),
                    'icon' => 'fas fa-user-check',
                    'cssClass' => 'obs-stat-manual',
                ]) ?>
            <?php endif; ?>
            <?= StatCard::widget([
                'label' => 'Publicadas',
                'value' => (int) $summary['published'],
                'icon' => 'fas fa-check-circle',
                'cssClass' => 'obs-stat-published',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Total',
                'value' => (int) $summary['total'],
                'icon' => 'fas fa-chart-line',
                'cssClass' => 'obs-stat-total',
            ]) ?>
        </div>
    </section>

    <section class="catalog-toolbar mb-4">
        <div class="toolbar-header">
            <h2 class="section-title">
                <i class="fas fa-filter" aria-hidden="true"></i>
                Pesquisar e filtrar
            </h2>
        </div>
        <form class="catalog-search mb-3" method="get" action="<?= Url::to(['observation/index']) ?>" role="search">
            <label class="search-field">
                <span class="search-icon" aria-hidden="true">
                    <i class="fas fa-search"></i>
                </span>
                <input
                    type="search"
                    name="q"
                    value="<?= Html::encode($queryText) ?>"
                    placeholder="Pesquisar por espÃ©cie"
                    aria-label="Pesquisar observaÃ§Ãµes por espÃ©cie"
                >
            </label>
            <?php if ($status !== 'all'): ?>
                <input type="hidden" name="status" value="<?= Html::encode($status) ?>">
            <?php endif; ?>
            <?php if ($myObservationsOnly): ?>
                <input type="hidden" name="my" value="1">
            <?php endif; ?>
            <button type="submit" class="btn btn-brand">
                <i class="fas fa-search" aria-hidden="true"></i>
                Pesquisar
            </button>
        </form>
        <div class="filter-row">
            <a class="btn <?= $status === 'all' && !$myObservationsOnly ? 'btn-brand' : 'btn-outline' ?>" href="<?= Url::to(['observation/index', 'status' => null, 'q' => $queryText ?: null, 'my' => 0]) ?>">
                <i class="fas fa-list" aria-hidden="true"></i>
                Todas
            </a>
            <a class="btn <?= $myObservationsOnly && $status === 'all' ? 'btn-brand' : 'btn-outline' ?>" href="<?= Url::to(['observation/index', 'my' => 1, 'status' => null, 'q' => $queryText ?: null]) ?>">
                <i class="fas fa-user" aria-hidden="true"></i>
                Minhas observaÃ§Ãµes
            </a>
            <a class="btn <?= $status === 'PUBLISHED' ? 'btn-brand' : 'btn-outline' ?>" href="<?= Url::to(['observation/index', 'status' => 'PUBLISHED', 'q' => $queryText ?: null, 'my' => 0]) ?>">
                <i class="fas fa-star" aria-hidden="true"></i>
                Publicadas
            </a>
            <?php if ($isAdmin): ?>
                <a class="btn <?= $status === Observation::STATUS_MANUAL_REVIEW ? 'btn-brand' : 'btn-outline' ?>" href="<?= Url::to(['observation/index', 'status' => Observation::STATUS_MANUAL_REVIEW, 'q' => $queryText ?: null, 'my' => $myObservationsOnly ? 1 : 0]) ?>">
                    <i class="fas fa-user-check" aria-hidden="true"></i>
                    RevisÃ£o manual
                </a>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($isAdmin): ?>
        <section class="toolbar-card admin-action-card mb-4">
            <div class="toolbar-row">
                <div class="admin-action-content">
                    <div class="admin-action-icon">
                        <i class="fas fa-plus-circle" aria-hidden="true"></i>
                    </div>
                    <div>
                        <strong>Criar observaÃ§Ã£o manual</strong>
                        <p class="table-subtext mb-0">TambÃ©m podes abrir a fila das observaÃ§Ãµes onde o MobileNet nÃ£o reconheceu a planta para completar a identificaÃ§Ã£o manualmente.</p>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <a class="btn btn-brand" href="<?= Url::to(['observation/create']) ?>" title="Criar nova observaÃ§Ã£o">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Nova observaÃ§Ã£o
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (empty($observations)): ?>
        <?= EmptyState::widget([
            'icon' => 'fas fa-inbox',
            'title' => 'Nenhuma observaÃ§Ã£o encontrada',
            'message' => 'Nenhuma observaÃ§Ã£o corresponde aos filtros selecionados.',
            'actions' => [
                ['label' => 'Voltar', 'url' => ['observation/index', 'my' => $myObservationsOnly ? 1 : 0], 'icon' => 'fas fa-redo', 'class' => 'btn-outline-brand'],
                ['label' => 'Nova observaÃ§Ã£o', 'url' => ['observation/create'], 'icon' => 'fas fa-plus', 'class' => 'btn-brand'],
            ],
        ]) ?>
    <?php else: ?>
        <section class="observations-list" role="region" aria-label="Lista de observaÃ§Ãµes">
            <?php foreach ($observations as $observation): ?>
                <?php
                if ($observation->is_published) {
                    $statusLabel = 'Publicada';
                    $statusClass = 'badge-success';
                    $statusIcon = 'fas fa-check-circle';
                } elseif ($observation->needsManualReview()) {
                    $statusLabel = 'RevisÃ£o manual';
                    $statusClass = 'badge-warning';
                    $statusIcon = 'fas fa-user-check';
                } elseif ($observation->sync_status === Observation::SYNC_SYNCED) {
                    $statusLabel = 'Sincronizada';
                    $statusClass = '';
                    $statusIcon = 'fas fa-sync';
                } elseif ($observation->sync_status === Observation::SYNC_FAILED) {
                    $statusLabel = 'Falha de sincronizaÃ§Ã£o';
                    $statusClass = 'badge-danger';
                    $statusIcon = 'fas fa-exclamation-triangle';
                } else {
                    $statusLabel = 'Pendente';
                    $statusClass = 'badge-warning';
                    $statusIcon = 'fas fa-hourglass-half';
                }

                $confidence = $observation->confidence !== null ? (int) round($observation->confidence * 100) : null;
                $confidenceClass = $confidence !== null
                    ? ($confidence >= 80 ? 'confidence-high' : ($confidence >= 50 ? 'confidence-medium' : 'confidence-low'))
                    : 'confidence-unknown';
                $title = $observation->needsManualReview()
                    ? 'ObservaÃ§Ã£o por identificar'
                    : ($observation->getResolvedCommonName() ?: 'ObservaÃ§Ã£o botÃ¢nica');
                $subtitle = $observation->getResolvedScientificName() ?: 'Sem classificaÃ§Ã£o';
                ?>
                <article class="timeline-item observation-item">
                    <div class="timeline-item-header">
                        <div>
                            <h3 class="timeline-item-title">
                                <i class="fas fa-leaf" aria-hidden="true"></i>
                                <?= Html::encode($title) ?>
                            </h3>
                            <p class="timeline-item-subtitle" lang="la">
                                <i class="fas fa-tag" aria-hidden="true"></i>
                                <?= Html::encode($subtitle) ?>
                            </p>
                            <?php $publishedBy = $observation->publication?->user?->getFullName(); ?>
                            <?php if ($observation->is_published && $publishedBy !== null && trim($publishedBy) !== ''): ?>
                                <p class="timeline-item-subtitle">
                                    <i class="fas fa-newspaper" aria-hidden="true"></i>
                                    <?= Html::encode('Publicado por ' . $publishedBy) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <span class="timeline-item-badge <?= $statusClass ?>">
                            <i class="<?= $statusIcon ?>" aria-hidden="true"></i>
                            <?= Html::encode($statusLabel) ?>
                        </span>
                    </div>

                    <div class="timeline-item-meta">
                        <div class="timeline-item-meta-item">
                            <span class="timeline-item-meta-label">
                                <i class="fas fa-calendar" aria-hidden="true"></i>
                                Data
                            </span>
                            <strong class="timeline-item-meta-value">
                                <?= Html::encode(Yii::$app->formatter->asDatetime($observation->observed_at, 'php:d/m/Y H:i')) ?>
                            </strong>
                        </div>
                        <div class="timeline-item-meta-item">
                            <span class="timeline-item-meta-label">
                                <i class="fas fa-chart-pie" aria-hidden="true"></i>
                                Confianca
                            </span>
                            <strong class="timeline-item-meta-value <?= $confidenceClass ?>">
                                <?= $confidence !== null ? $confidence . '%' : 'N/D' ?>
                            </strong>
                        </div>
                        <div class="timeline-item-meta-item">
                            <span class="timeline-item-meta-label">
                                <i class="fas fa-dna" aria-hidden="true"></i>
                                Especie
                            </span>
                            <strong class="timeline-item-meta-value" title="<?= Html::encode($subtitle) ?>">
                                <?= Html::encode($observation->needsManualReview() ? 'A aguardar identificaÃ§Ã£o manual' : ($observation->getResolvedCommonName() ?? $subtitle ?? 'NÃ£o associada')) ?>
                            </strong>
                        </div>
                    </div>

                    <div class="timeline-item-actions">
                        <?php if (!empty($observation->plant_species_id)): ?>
                            <a href="<?= Url::to(['species/view', 'id' => $observation->plant_species_id]) ?>" class="timeline-item-action-link" title="Ver detalhes da especie">
                                <i class="fas fa-leaf" aria-hidden="true"></i>
                                Ver especie
                            </a>
                        <?php endif; ?>
                        <a href="<?= Url::to(['observation/view', 'id' => $observation->observation_id]) ?>" class="timeline-item-action-link" title="Ver detalhes da observaÃ§Ã£o">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                            Ver observaÃ§Ã£o
                        </a>
                        <?php if (
                            $observation->observation_id !== null
                            && (
                                ($observation->needsManualReview() && (Yii::$app->user->identity?->isAdmin() ?? false))
                                || (!$observation->needsManualReview() && ((Yii::$app->user->identity?->isAdmin() ?? false) || Yii::$app->user->id === $observation->user_id))
                            )
                        ): ?>
                            <a href="<?= Url::to(['observation/update', 'id' => $observation->observation_id]) ?>" class="timeline-item-action-link" title="<?= $observation->needsManualReview() ? 'Completar identificaÃ§Ã£o manual' : 'Editar observaÃ§Ã£o' ?>">
                                <i class="<?= $observation->needsManualReview() ? 'fas fa-user-check' : 'fas fa-edit' ?>" aria-hidden="true"></i>
                                <?= $observation->needsManualReview() ? 'Identificar' : 'Editar' ?>
                            </a>
                        <?php endif; ?>
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
                'prevPageLabel' => 'â€¹',
                'nextPageLabel' => 'â€º',
                'hideOnSinglePage' => true,
                'disabledListItemSubTagOptions' => ['class' => 'd-none'],
            ]) ?>
        </div>
    <?php endif; ?>
</div>

<style>
.observations-list {
    display: grid;
    gap: 1rem;
    margin-bottom: 2rem;
}

.observation-item {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.admin-action-card .toolbar-row {
    gap: 1.5rem;
    align-items: flex-start;
}

.admin-action-content {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    flex: 1;
}

.admin-action-icon {
    font-size: 2.5rem;
    color: var(--gf-primary);
    min-width: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toolbar-header {
    margin-bottom: 1rem;
}

.toolbar-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--gf-text);
    font-weight: 600;
    font-size: 0.95rem;
}
</style>
