<?php

use app\components\StatCard;
use app\components\EmptyState;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Dashboard';
?>
<div class="module-shell">
    <!-- Hero Section -->
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-home" aria-hidden="true"></i>
                Dashboard
            </span>
            <h1 class="hero-title hero-title-tight">Bem-vindo ao GeoFlora</h1>
            <p class="hero-text">Portal de administração, planeamento e monitorização botânica</p>
            
            <?php if (Yii::$app->user->isGuest): ?>
                <div class="hero-cta-row mt-4">
                    <a class="btn btn-brand" href="<?= Url::to(['site/login']) ?>">
                        <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                        Entrar
                    </a>
                    <a class="btn btn-outline" href="<?= Url::to(['site/signup']) ?>">
                        <i class="fas fa-user-plus" aria-hidden="true"></i>
                        Criar conta
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="detail-stat-grid">
            <?= StatCard::widget([
                'label' => 'Espécies',
                'value' => (int) $speciesCount,
                'icon' => 'fas fa-leaf',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Observações',
                'value' => (int) $observationCount,
                'icon' => 'fas fa-binoculars',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Publicações',
                'value' => (int) $publicationCount,
                'icon' => 'fas fa-newspaper',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Utilizadores',
                'value' => (int) $userCount,
                'icon' => 'fas fa-users',
            ]) ?>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert-success-custom mb-4">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <!-- Modules Section -->
    <section class="mt-6">
        <div class="section-header mb-4">
            <h2 class="section-title">
                <i class="fas fa-cube" aria-hidden="true"></i>
                Módulos Disponíveis
            </h2>
            <p class="section-description">Aceda aos diferentes módulos da plataforma</p>
        </div>

        <div class="modules-grid">
            <!-- Species -->
            <div class="module-card">
                <div class="module-icon species">
                    <i class="fas fa-leaf" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Espécies</h3>
                    <p>Pesquisa por espécie, família e género com hierarquia de leitura.</p>
                </div>
                <a href="<?= Url::to(['species/index']) ?>" class="module-link">
                    Explorar catálogo
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            <!-- Observations -->
            <div class="module-card">
                <div class="module-icon observation">
                    <i class="fas fa-binoculars" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Observações</h3>
                    <p>Segue estados de sincronização, confiança, autoria e publicações.</p>
                </div>
                <a href="<?= Url::to(['observation/index']) ?>" class="module-link">
                    Ver observações
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            <!-- Publications -->
            <div class="module-card">
                <div class="module-icon publication">
                    <i class="fas fa-newspaper" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Publicações</h3>
                    <p>Cria, edita e publica conteúdo editorial a partir das observações.</p>
                </div>
                <a href="<?= Url::to(['publication/index']) ?>" class="module-link">
                    Ver publicações
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            <!-- Visits -->
            <div class="module-card">
                <div class="module-icon visit">
                    <i class="fas fa-route" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Quero Visitar</h3>
                    <p>Marca espécies e publicações e transforma em objetivos de visita.</p>
                </div>
                <?php if (Yii::$app->user->isGuest): ?>
                    <a href="<?= Url::to(['site/login']) ?>" class="module-link">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        Entrar para aceder
                    </a>
                <?php else: ?>
                    <a href="<?= Url::to(['visit/index']) ?>" class="module-link">
                        Para Visitar
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Routes -->
            <div class="module-card">
                <div class="module-icon route">
                    <i class="fas fa-route" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Percursos</h3>
                    <p>Cria percursos planeados com ordem de visita para integrar na mobile.</p>
                </div>
                <?php if (Yii::$app->user->isGuest): ?>
                    <a href="<?= Url::to(['site/login']) ?>" class="module-link">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                        Entrar para aceder
                    </a>
                <?php else: ?>
                    <a href="<?= Url::to(['route-plan/index']) ?>" class="module-link">
                        Ver os meus percursos
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Map -->
            <div class="module-card">
                <div class="module-icon map">
                    <i class="fas fa-map" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Mapa Interativo</h3>
                    <p>Visualiza todas as observações e espécies num mapa interativo.</p>
                </div>
                <a href="<?= Url::to(['map/index']) ?>" class="module-link">
                    Abrir mapa
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </section>
</div>
</section>
