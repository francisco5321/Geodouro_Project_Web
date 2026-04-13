<?php
/** @var yii\web\View $this */
/** @var array<int, array>|app\models\RoutePlan[] $plans */
/** @var yii\data\Pagination|null $pagination */
/** @var string|null $backendError */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Percursos';
$planCount = is_array($plans) ? count($plans) : 0;
?>
<div class="module-shell">

    <!-- Hero melhorado: ícone decorativo + stats mais destacadas -->
    <section class="species-hero mb-4">
        <div class="species-detail-copy">
            <span class="eyebrow">Planeamento</span>
            <h1 class="hero-title">Percursos planeados</h1>
            <p class="hero-text">Organiza os teus alvos de visita por ordem e prepara percursos que mais tarde podem ser seguidos no mobile.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card">
                <span>Percursos</span>
                <strong><?= $planCount ?></strong>
            </article>
            <article class="detail-stat-card">
                <span>Origem</span>
                <strong style="font-size: 1.1rem; margin-top: 0.4rem;">
                    <?= $backendError === null ? 'Backend comum' : 'Fallback local' ?>
                </strong>
            </article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if ($backendError !== null): ?>
        <div class="alert alert-warning alert-geoflora mb-4">
            A listagem foi carregada pela base de dados local — o backend não respondeu:
            <strong><?= Html::encode($backendError) ?></strong>
        </div>
    <?php endif; ?>

    <!-- Toolbar: contagem + botão criar -->
    <div class="toolbar-card mb-4">
        <div class="toolbar-row">
            <div class="d-flex align-items-center gap-2">
                <span class="species-meta-chip chip-highlight"><?= $planCount ?> percurso<?= $planCount !== 1 ? 's' : '' ?></span>
            </div>
            <div class="toolbar-actions">
                <?= Html::a('+ Novo percurso', Url::to(['route-plan/create']), ['class' => 'btn btn-brand btn-sm']) ?>
            </div>
        </div>
    </div>

    <!-- Grid de percursos -->
    <section class="publication-grid">

        <?php if (empty($plans)): ?>
            <div class="empty-state-card content-card w-100 text-center py-5">
                <div class="mb-3" style="font-size: 2.5rem; opacity: .35;">🗺️</div>
                <h3 class="mb-2">Ainda não tens percursos</h3>
                <p class="hero-text mx-auto mb-4">
                    Cria o teu primeiro percurso e começa a ordenar as plantas e publicações que queres visitar no terreno.
                </p>
                <?= Html::a('Criar percurso', Url::to(['route-plan/create']), ['class' => 'btn btn-brand']) ?>
            </div>
        <?php endif; ?>

        <?php foreach ($plans as $plan): ?>
            <?php
            $isApiPlan  = is_array($plan);
            $routePlanId = $isApiPlan ? (int)($plan['routePlanId'] ?? 0)   : (int)$plan->route_plan_id;
            $name        = $isApiPlan ? (string)($plan['name'] ?? 'Percurso sem nome') : (string)$plan->name;
            $description = $isApiPlan ? ($plan['description'] ?? null)      : $plan->description;
            $stopCount   = $isApiPlan ? (int)($plan['stopCount'] ?? 0)      : count($plan->routePlanPoints);
            ?>
            <article class="publication-card publication-card-rich">

                <!-- Cabeçalho colorido com ícone SVG -->
                <div class="species-card-media" style="min-height: 90px; padding: 1.1rem 1.35rem; align-items: center;">
                    <div class="species-orb" style="width: 120px; height: 120px; right: -20px; top: -20px;"></div>
                    <div class="species-media-copy d-flex align-items-center gap-3">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                             stroke="rgba(255,255,255,0.9)" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/>
                            <circle cx="19" cy="19" r="2"/>
                            <line x1="12" y1="7" x2="5" y2="17"/>
                            <line x1="12" y1="7" x2="19" y2="17"/>
                        </svg>
                        <div>
                            <span class="species-media-label">Percurso</span>
                            <strong style="font-size: 1.05rem;"><?= Html::encode($name) ?></strong>
                        </div>
                    </div>
                </div>

                <div class="publication-card-body">

                    <!-- Badges de metadados -->
                    <div class="card-chip-row mb-3">
                        <span class="species-meta-chip chip-highlight"><?= $stopCount ?> paragem<?= $stopCount !== 1 ? 's' : '' ?></span>
                        <?php if ($stopCount > 0): ?>
                            <span class="species-meta-chip">
                                <?= str_repeat('● ', min($stopCount, 5)) ?>
                                <?php if ($stopCount > 5): ?>+<?= $stopCount - 5 ?><?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <p class="publication-copy">
                        <?= Html::encode($description ?: 'Sem descrição definida para este percurso.') ?>
                    </p>

                    <!-- Acções com separação visual clara -->
                    <div class="timeline-card-actions" style="margin-top: 1.1rem; padding-top: 0.9rem; border-top: 1px solid rgba(62,122,87,0.08);">
                        <?= Html::a('Abrir percurso →', Url::to(['route-plan/view',   'id' => $routePlanId]), ['class' => 'btn btn-brand btn-sm']) ?>
                        <?= Html::a('Editar',           Url::to(['route-plan/update', 'id' => $routePlanId]), ['class' => 'btn btn-outline-brand btn-sm']) ?>
                    </div>

                </div>
            </article>
        <?php endforeach; ?>

    </section>

    <?php if ($pagination !== null): ?>
        <div class="catalog-pagination mt-3">
            <?= LinkPager::widget([
                'pagination'          => $pagination,
                'options'             => ['class' => 'pagination justify-content-center mb-0'],
                'linkOptions'         => ['class' => 'page-link'],
                'pageCssClass'        => 'page-item',
                'prevPageCssClass'    => 'page-item',
                'nextPageCssClass'    => 'page-item',
                'disabledPageCssClass'=> 'page-item disabled',
                'activePageCssClass'  => 'page-item active',
            ]) ?>
        </div>
    <?php endif; ?>

</div>