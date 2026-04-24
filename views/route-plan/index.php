<?php
/** @var yii\web\View $this */
/** @var array<int, array>|app\models\RoutePlan[] $plans */
/** @var yii\data\Pagination|null $pagination */
/** @var string|null $backendError */
/** @var app\models\RoutePlan $newPlan */

use app\components\StatCard;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

$this->title = 'Percursos';
?>
<div class="module-shell">

    <!-- Hero melhorado: ícone decorativo + stats mais destacadas -->
    <section class="species-hero route-plan-hero mb-4">
        <div class="species-detail-copy">
            <span class="eyebrow">
                <i class="fas fa-route" aria-hidden="true"></i>
                Planeamento
            </span>
            <h1 class="hero-title hero-title-tight">Percursos Planeados</h1>
            <p class="hero-text">Organiza os teus alvos de visita por ordem e prepara percursos que podem ser seguidos no telemóvel em expedição no terreno.</p>
        </div>
        <div class="detail-stat-grid">
            <?= StatCard::widget([
                'label' => 'Percursos',
                'value' => is_array($plans) ? count($plans) : 0,
                'icon' => 'fas fa-map-pin',
            ]) ?>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert-success-custom mb-4">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if ($backendError !== null): ?>
        <div class="alert-danger-custom mb-4">
            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
            A listagem foi carregada pela base de dados local — o backend não respondeu:
            <strong><?= Html::encode($backendError) ?></strong>
        </div>
    <?php endif; ?>

    <!-- Toolbar: contagem + botão criar -->
    <section class="catalog-toolbar mb-4 visit-route-builder-card">
        <div class="toolbar-header">
            <h2 class="section-title">
                <i class="fas fa-plus-circle" aria-hidden="true"></i>
                Criar Percurso
            </h2>
            <p class="section-description mb-0">Cria primeiro o percurso e depois adiciona as paragens no mapa do detalhe.</p>
        </div>
        <?= Html::beginForm(['route-plan/create'], 'post', ['class' => 'visit-route-builder-form']) ?>
            <div class="visit-route-builder-grid">
                <div>
                    <?= Html::activeLabel($newPlan, 'name', ['class' => 'form-label']) ?>
                    <?= Html::activeTextInput($newPlan, 'name', [
                        'class' => 'form-control',
                        'placeholder' => 'Ex.: Plantas ribeirinhas do Pocinho',
                        'maxlength' => true,
                    ]) ?>
                </div>
                <div>
                    <?= Html::activeLabel($newPlan, 'description', ['class' => 'form-label']) ?>
                    <?= Html::activeTextarea($newPlan, 'description', [
                        'class' => 'form-control route-plan-description-input',
                        'rows' => 1,
                        'placeholder' => 'Objetivo do percurso, especies a validar e notas para a visita de campo.',
                    ]) ?>
                </div>
            </div>
            <div class="visit-route-builder-actions">
                <span class="section-description mb-0">O percurso fica criado sem paragens. Abre o detalhe para escolher os pontos no mapa.</span>
                <?= Html::submitButton('Criar Percurso', ['class' => 'btn btn-brand']) ?>
            </div>
        <?= Html::endForm() ?>
    </section>

    <div class="catalog-toolbar mb-4">
        <div class="toolbar-header">
            <h2 class="section-title">
                <i class="fas fa-list" aria-hidden="true"></i>
                Os teus Percursos
            </h2>
        </div>
    </div>

    <!-- Grid de percursos -->
    <section class="publication-grid">

        <?php if (empty($plans)): ?>
            <div class="empty-state-card content-card route-plan-empty-state text-center py-5">
                <div class="mb-3" style="font-size: 2.5rem; opacity: .35;">
                    <i class="fas fa-sitemap" aria-hidden="true"></i>
                </div>
                <h3 class="mb-2">Ainda não tens percursos</h3>
                <p class="hero-text mx-auto mb-0">
                    Cria o teu primeiro percurso aqui e depois abre o detalhe para adicionar paragens no mapa.
                </p>
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
            <article class="route-plan-card">

                <!-- Header com ícone e informações -->
                <div class="route-card-header">
                    <div class="route-card-icon">
                        <i class="fas fa-route" aria-hidden="true"></i>
                    </div>
                    <div class="route-card-info">
                        <h3><?= Html::encode($name) ?></h3>
                        <p class="route-card-subtitle">
                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                            <?= $stopCount ?> paragem<?= $stopCount !== 1 ? 's' : '' ?>
                        </p>
                    </div>
                </div>

                <!-- Descrição -->
                <div class="route-card-body">
                    <p class="route-card-description">
                        <?= Html::encode($description ?: 'Sem descrição definida.') ?>
                    </p>
                    <?php if ($stopCount === 0): ?>
                        <p class="route-card-description route-empty-stops">
                            Ainda nao tem paragens. Adiciona pontos no mapa para comecares a montar o percurso.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Rodapé com ações -->
                <div class="route-card-footer">
                    <div class="route-card-stops">
                        <?php if ($stopCount > 0): ?>
                            <span class="stops-indicator">
                                <?= str_repeat('●', min($stopCount, 5)) ?>
                                <?php if ($stopCount > 5): ?><span class="stops-more">+<?= $stopCount - 5 ?></span><?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="route-card-actions">
                        <?php if ($stopCount === 0): ?>
                            <?= Html::a('Adicionar paragens', Url::to(['route-plan/view', 'id' => $routePlanId]), ['class' => 'btn btn-outline route-add-stops-button']) ?>
                        <?php endif; ?>
                        <?= Html::a('Abrir', Url::to(['route-plan/view',   'id' => $routePlanId]), ['class' => 'btn-link']) ?>
                        <?= Html::a('Editar', Url::to(['route-plan/update', 'id' => $routePlanId]), ['class' => 'btn-link']) ?>
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
                'prevPageLabel'       => '‹',
                'nextPageLabel'       => '›',
                'hideOnSinglePage'    => true,
                'disabledListItemSubTagOptions' => ['class' => 'd-none'],
            ]) ?>
        </div>
    <?php endif; ?>

</div>
