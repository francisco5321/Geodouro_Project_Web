<?php

use app\components\StatCard;
use app\components\FilterChips;
use app\components\EmptyState;
use app\components\TimelineCard;
use app\models\Observation;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var Observation[] $observations */
/** @var yii\data\Pagination $pagination */
/** @var string $status */
/** @var array $summary */

$this->title = 'Observações';
?>
<div class="module-shell">
    <!-- Hero Section -->
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-binoculars" aria-hidden="true"></i>
                Módulo de observações
            </span>
            <h1 class="hero-title hero-title-tight">Registos captados e sincronizados a partir da app mobile</h1>
            <p class="hero-text">Visualiza, filtra e gerencia todas as observações botânicas recolhidas pelo projeto GeoFlora.</p>
        </div>
        <div class="detail-stat-grid">
            <?= StatCard::widget([
                'label' => 'Total',
                'value' => (int) $summary['total'],
                'icon' => 'fas fa-chart-line',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Publicadas',
                'value' => (int) $summary['published'],
                'icon' => 'fas fa-check-circle',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Pendentes',
                'value' => (int) $summary['pending'],
                'icon' => 'fas fa-hourglass-half',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Falhadas',
                'value' => (int) $summary['failed'],
                'icon' => 'fas fa-exclamation-circle',
            ]) ?>
        </div>
    </section>

    <!-- Toolbar com Filtros -->
    <section class="catalog-toolbar mb-4">
        <div class="toolbar-header">
            <h2 class="section-title">
                <i class="fas fa-filter" aria-hidden="true"></i>
                Filtrar por Status
            </h2>
        </div>
        <div class="filter-row">
            <?php 
            $filterChips = [];
            foreach (['all' => ['label' => 'Todas', 'icon' => 'fas fa-list'],
                      Observation::SYNC_PENDING => ['label' => 'Pendentes', 'icon' => 'fas fa-clock'],
                      Observation::SYNC_SYNCED => ['label' => 'Sincronizadas', 'icon' => 'fas fa-sync'],
                      Observation::SYNC_FAILED => ['label' => 'Falhadas', 'icon' => 'fas fa-times-circle'],
                      'PUBLISHED' => ['label' => 'Publicadas', 'icon' => 'fas fa-star']] as $value => $config):
                $filterChips[] = [
                    'label' => $config['label'],
                    'url' => ['observation/index', 'status' => $value === 'all' ? null : $value],
                    'active' => $status === $value,
                    'icon' => $config['icon'],
                ];
            endforeach;
            ?>
            <?= FilterChips::widget(['chips' => $filterChips]) ?>
        </div>
    </section>

    <!-- Secção de Admin: Criar Observação Manual -->
    <?php if (Yii::$app->user->identity?->isAdmin()): ?>
        <section class="toolbar-card admin-action-card mb-4">
            <div class="toolbar-row">
                <div class="admin-action-content">
                    <div class="admin-action-icon">
                        <i class="fas fa-plus-circle" aria-hidden="true"></i>
                    </div>
                    <div>
                        <strong>Criar Observação Manual</strong>
                        <p class="table-subtext mb-0">Como administrador, podes criar uma observação manual a partir do mapa ou abrir diretamente o formulário.</p>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <a class="btn btn-outline-brand" href="<?= Url::to(['map/index']) ?>" title="Abrir mapa interativo">
                        <i class="fas fa-map" aria-hidden="true"></i>
                        Abrir mapa
                    </a>
                    <a class="btn btn-brand" href="<?= Url::to(['observation/create']) ?>" title="Criar nova observação">
                        <i class="fas fa-plus" aria-hidden="true"></i>
                        Nova observação
                    </a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- Lista de Observações -->
    <?php if (empty($observations)): ?>
        <?= EmptyState::widget([
            'icon' => 'fas fa-inbox',
            'title' => 'Nenhuma observação encontrada',
            'message' => 'Nenhuma observação corresponde aos filtros selecionados. Tenta mudar os filtros ou cria uma nova observação.',
            'actions' => [
                ['label' => 'Volta aos filtros', 'url' => ['observation/index'], 'icon' => 'fas fa-redo', 'class' => 'btn-outline-brand'],
                ['label' => 'Nova observação', 'url' => ['observation/create'], 'icon' => 'fas fa-plus', 'class' => 'btn-brand'],
            ],
        ]) ?>
    <?php else: ?>
        <section class="observations-list" role="region" aria-label="Lista de observações">
            <?php foreach ($observations as $observation): ?>
                <?php 
                // Determinar status e classe CSS
                if ($observation->is_published) {
                    $statusLabel = 'Publicada';
                    $statusClass = 'badge-success';
                    $statusIcon = 'fas fa-check-circle';
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
                
                // Calcular confiança com classe visual
                $confidence = $observation->confidence !== null ? (int) round($observation->confidence * 100) : null;
                $confidenceClass = $confidence !== null ? 
                    ($confidence >= 80 ? 'confidence-high' : ($confidence >= 50 ? 'confidence-medium' : 'confidence-low')) : 
                    'confidence-unknown';
                ?>
                <article class="timeline-item observation-item">
                    <div class="timeline-item-header">
                        <div>
                            <h3 class="timeline-item-title">
                                <i class="fas fa-leaf" aria-hidden="true"></i>
                                <?= Html::encode($observation->getResolvedCommonName() ?: 'Observação botânica') ?>
                            </h3>
                            <p class="timeline-item-subtitle" lang="la">
                                <i class="fas fa-tag" aria-hidden="true"></i>
                                <?= Html::encode($observation->getResolvedScientificName() ?: 'Sem classificação') ?>
                            </p>
                        </div>
                        <span class="timeline-item-badge <?= $statusClass ?>">
                            <i class="<?= $statusIcon ?>" aria-hidden="true"></i>
                            <?= Html::encode($statusLabel) ?>
                        </span>
                    </div>

                    <div class="timeline-item-meta">
                        <div class="timeline-item-meta-item">
                            <span class="timeline-item-meta-label">
                                <i class="fas fa-user" aria-hidden="true"></i>
                                Autor
                            </span>
                            <strong class="timeline-item-meta-value">
                                <?= Html::encode($observation->user?->getFullName() ?? 'Sistema') ?>
                            </strong>
                        </div>
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
                                Confiança
                            </span>
                            <strong class="timeline-item-meta-value <?= $confidenceClass ?>">
                                <?= $confidence !== null ? $confidence . '%' : 'N/D' ?>
                            </strong>
                        </div>
                        <div class="timeline-item-meta-item">
                            <span class="timeline-item-meta-label">
                                <i class="fas fa-dna" aria-hidden="true"></i>
                                Espécie
                            </span>
                            <strong class="timeline-item-meta-value" title="<?= Html::encode($observation->plantSpecies?->scientific_name ?? 'Não associada') ?>">
                                <?= Html::encode($observation->plantSpecies?->common_name ?? $observation->plantSpecies?->scientific_name ?? 'Não associada') ?>
                            </strong>
                        </div>
                    </div>

                    <div class="timeline-item-actions">
                        <?php if ($observation->plant_species_id): ?>
                            <a href="<?= Url::to(['species/view', 'id' => $observation->plant_species_id]) ?>" 
                               class="timeline-item-action-link" 
                               title="Ver detalhes da espécie">
                                <i class="fas fa-leaf" aria-hidden="true"></i>
                                Ver espécie
                            </a>
                        <?php endif; ?>
                        <a href="<?= Url::to(['observation/view', 'id' => $observation->observation_id]) ?>" 
                           class="timeline-item-action-link" 
                           title="Ver detalhes da observação">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                            Ver observação
                        </a>
                        <?php if (Yii::$app->user->identity?->isAdmin() || Yii::$app->user->id === $observation->user_id): ?>
                            <a href="<?= Url::to(['observation/update', 'id' => $observation->observation_id]) ?>" 
                               class="timeline-item-action-link" 
                               title="Editar observação">
                                <i class="fas fa-edit" aria-hidden="true"></i>
                                Editar
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- Paginação -->
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

<style>
/* Estilos específicos para observações */
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

/* Confiança visual */
.confidence-high {
    color: #28a745;
    font-weight: 700;
}

.confidence-medium {
    color: #ffc107;
    font-weight: 700;
}

.confidence-low {
    color: #dc3545;
    font-weight: 700;
}

.confidence-unknown {
    color: var(--gf-muted);
}

/* Badges de status melhorados */
.timeline-item-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    background: var(--gf-surface-soft);
    color: var(--gf-muted);
    white-space: nowrap;
}

.timeline-item-badge.badge-success {
    background: #d4edda;
    color: #155724;
}

.timeline-item-badge.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.timeline-item-badge.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

/* Responsive */
@media (max-width: 767.98px) {
    .admin-action-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .toolbar-row {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .toolbar-actions {
        flex-direction: column !important;
        width: 100%;
    }

    .toolbar-actions .btn {
        width: 100%;
        justify-content: center;
    }

    .observation-item .timeline-item-header {
        flex-direction: column !important;
        align-items: flex-start !important;
    }

    .timeline-item-badge {
        align-self: flex-start;
    }

    .timeline-item-meta {
        grid-template-columns: 1fr !important;
    }
}
</style>
