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
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Planeamento</span>
            <h1 class="hero-title hero-title-tight">Percursos planeados</h1>
            <p class="hero-text">Organiza os teus alvos de visita por ordem e prepara percursos que mais tarde podem ser seguidos no mobile.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Percursos</span><strong><?= $planCount ?></strong></article>
            <article class="detail-stat-card"><span>Origem</span><strong><?= $backendError === null ? 'Backend comum' : 'Fallback local' ?></strong></article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <?php if ($backendError !== null): ?>
        <div class="alert alert-warning alert-geoflora mb-4">
            A listagem de percursos foi carregada pela base de dados local porque o backend comum nao respondeu: <?= Html::encode($backendError) ?>
        </div>
    <?php endif; ?>

    <section class="toolbar-card mb-4">
        <div class="toolbar-row">
            <div>
                <strong>Baseada em Quero visitar</strong>
                <p class="table-subtext mb-0">Cria um percurso e depois escolhe os alvos que queres visitar primeiro.</p>
            </div>
            <div class="toolbar-actions">
                <a class="btn btn-outline-brand" href="<?= Url::to(['visit/index']) ?>">Abrir Quero visitar</a>
                <a class="btn btn-brand" href="<?= Url::to(['route-plan/create']) ?>">Novo percurso</a>
            </div>
        </div>
    </section>

    <section class="publication-grid">
        <?php if (empty($plans)): ?>
            <div class="empty-state-card w-100">
                <h3>Ainda nao tens percursos</h3>
                <p>Cria o teu primeiro percurso e começa a ordenar as plantas e publicacoes que queres visitar no terreno.</p>
            </div>
        <?php endif; ?>
        <?php foreach ($plans as $plan): ?>
            <?php
            $isApiPlan = is_array($plan);
            $routePlanId = $isApiPlan ? (int) ($plan['routePlanId'] ?? 0) : (int) $plan->route_plan_id;
            $name = $isApiPlan ? (string) ($plan['name'] ?? 'Percurso sem nome') : (string) $plan->name;
            $description = $isApiPlan ? ($plan['description'] ?? null) : $plan->description;
            $stopCount = $isApiPlan ? (int) ($plan['stopCount'] ?? 0) : count($plan->routePlanPoints);
            ?>
            <article class="publication-card publication-card-rich">
                <div class="publication-card-body">
                    <div class="card-chip-row mb-2">
                        <span class="species-meta-chip chip-highlight">Percurso</span>
                        <span class="species-meta-chip"><?= $stopCount ?> paragens</span>
                    </div>
                    <h2><?= Html::encode($name) ?></h2>
                    <p class="publication-copy"><?= Html::encode($description ?: 'Sem descricao definida para este percurso.') ?></p>
                    <div class="timeline-card-actions">
                        <a href="<?= Url::to(['route-plan/view', 'id' => $routePlanId]) ?>">Abrir percurso</a>
                        <a href="<?= Url::to(['route-plan/update', 'id' => $routePlanId]) ?>">Editar</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <?php if ($pagination !== null): ?>
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
