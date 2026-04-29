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

$this->title = 'Observações';
?>
<?php $isAdmin = Yii::$app->user->identity?->isAdmin() ?? false; ?>
<div class="module-shell">
    <section class="species-hero observation-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-binoculars" aria-hidden="true"></i>
                ódulo de observações
            </span>
            <h1 class="hero-title hero-title-tight">Registos captados e sincronizados a partir da app mobile</h1>
            <p class="hero-text">Visualiza, filtra e gere todas as observações botânicas recolhidas pelo projeto GeoFlora.</p>
        </div>
        <div class="detail-stat-grid">
            <?php if ($isAdmin): ?>
                <?= StatCard::widget([
                    'label' => 'Revisão manual',
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
                    placeholder="Pesquisar por espécie"
                    aria-label="Pesquisar observações por espécie"
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
            <a class="btn <?= $myObservationsOnly ? 'btn-brand' : 'btn-outline' ?>" href="<?= Url::to(['observation/index', 'my' => 1, 'status' => $status === 'all' ? null : $status, 'q' => $queryText ?: null]) ?>">
                <i class="fas fa-user" aria-hidden="true"></i>
                Minhas observaÃ§Ãµes
            </a>
            <a class="btn <?= !$myObservationsOnly ? 'btn-brand' : 'btn-outline' ?>" href="<?= Url::to(['observation/index', 'my' => 0, 'status' => $status === 'all' ? null : $status, 'q' => $queryText ?: null]) ?>">
                <i class="fas fa-globe" aria-hidden="true"></i>
                Todas as observaÃ§Ãµes
            </a>
        </div>
        <div class="filter-row">
            <?php
            $statusFilters = [
                'all' => ['label' => 'Todas', 'icon' => 'fas fa-list'],
                'PUBLISHED' => ['label' => 'Publicadas', 'icon' => 'fas fa-star'],
            ];
            if ($isAdmin) {
                $statusFilters[Observation::STATUS_MANUAL_REVIEW] = ['label' => 'Revisão manual', 'icon' => 'fas fa-user-check'];
            }
            foreach ($statusFilters as $value => $config):
            ?>
                <a class="btn <?= $status === $value ? 'btn-brand' : 'btn-outline' ?>" href="<?= Url::to(['observation/index', 'status' => $value === 'all' ? null : $value, 'q' => $queryText ?: null, 'my' => $myObservationsOnly ? 1 : 0]) ?>">
                    <i class="<?= Html::encode($config['icon']) ?>" aria-hidden="true"></i>
                    <?= Html::encode($config['label']) ?>
                </a>
            <?php endforeach; ?>
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
                        <strong>Criar observação manual</strong>
                        <p class="table-subtext mb-0">Também podes abrir a fila das observações onde o MobileNet não reconheceu a planta para completar a identificação manualmente.</p>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <a class="btn btn-brand" href="<?= Url::to(['observation/create']) ?>" title="Criar nova observação">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Nova observação
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (empty($observations)): ?>
        <?= EmptyState::widget([
            'icon' => 'fas fa-inbox',
            'title' => 'Nenhuma observação encontrada',
            'message' => 'Nenhuma observação corresponde aos filtros selecionados.',
            'actions' => [
                ['label' => 'Voltar', 'url' => ['observation/index', 'my' => $myObservationsOnly ? 1 : 0], 'icon' => 'fas fa-redo', 'class' => 'btn-outline-brand'],
                ['label' => 'Nova observação', 'url' => ['observation/create'], 'icon' => 'fas fa-plus', 'class' => 'btn-brand'],
            ],
        ]) ?>
    <?php else: ?>
        <section class="observations-list" role="region" aria-label="Lista de observações">
            <?php foreach ($observations as $observation): ?>
                <?php
                if ($observation->is_published) {
                    $statusLabel = 'Publicada';
                    $statusClass = 'badge-success';
                    $statusIcon = 'fas fa-check-circle';
                } elseif ($observation->needsManualReview()) {
                    $statusLabel = 'Revisão manual';
                    $statusClass = 'badge-warning';
                    $statusIcon = 'fas fa-user-check';
                } elseif ($observation->sync_status === Observation::SYNC_SYNCED) {
                    $statusLabel = 'Sincronizada';
                    $statusClass = '';
                    $statusIcon = 'fas fa-sync';
                } elseif ($observation->sync_status === Observation::SYNC_FAILED) {
                    $statusLabel = 'Falha de sincronização';
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
                    ? 'Observação por identificar'
                    : ($observation->getResolvedCommonName() ?: 'Observação botânica');
                $subtitle = $observation->getResolvedScientificName() ?: 'Sem classificação';
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
                                <?= Html::encode($observation->needsManualReview() ? 'A aguardar identificação manual' : ($observation->getResolvedCommonName() ?? $subtitle ?? 'Não associada')) ?>
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
                        <a href="<?= Url::to(['observation/view', 'id' => $observation->observation_id]) ?>" class="timeline-item-action-link" title="Ver detalhes da observação">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                            Ver observação
                        </a>
                        <?php if ($observation->observation_id !== null && (Yii::$app->user->identity?->isAdmin() || Yii::$app->user->id === $observation->user_id)): ?>
                            <a href="<?= Url::to(['observation/update', 'id' => $observation->observation_id]) ?>" class="timeline-item-action-link" title="<?= $observation->needsManualReview() ? 'Completar identificação manual' : 'Editar observação' ?>">
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
                'prevPageLabel' => '‹',
                'nextPageLabel' => '›',
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
