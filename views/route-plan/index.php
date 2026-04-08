<?php

/** @var yii\web\View $this */
/** @var app\models\RoutePlan[] $plans */
/** @var yii\data\Pagination $pagination */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Percursos';
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Planeamento</span>
            <h1 class="hero-title hero-title-tight">Percursos planeados</h1>
            <p class="hero-text">Organiza os teus alvos de visita por ordem e prepara percursos que mais tarde podem ser seguidos no mobile.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Percursos</span><strong><?= count($plans) ?></strong></article>
            <article class="detail-stat-card"><span>Estado</span><strong>MVP</strong></article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
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
            <article class="publication-card publication-card-rich">
                <div class="publication-card-body">
                    <div class="card-chip-row mb-2">
                        <span class="species-meta-chip chip-highlight">Percurso</span>
                        <span class="species-meta-chip"><?= count($plan->routePlanPoints) ?> paragens</span>
                    </div>
                    <h2><?= Html::encode($plan->name) ?></h2>
                    <p class="publication-copy"><?= Html::encode($plan->description ?: 'Sem descricao definida para este percurso.') ?></p>
                    <div class="timeline-card-actions">
                        <a href="<?= Url::to(['route-plan/view', 'id' => $plan->route_plan_id]) ?>">Abrir percurso</a>
                        <a href="<?= Url::to(['route-plan/update', 'id' => $plan->route_plan_id]) ?>">Editar</a>
                    </div>
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
