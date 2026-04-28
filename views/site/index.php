<?php

use app\components\StatCard;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $speciesCount */
/** @var int $observationCount */
/** @var int $manualReviewCount */
/** @var int $publicationCount */
/** @var int $userCount */

$this->title = 'Dashboard';
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-home" aria-hidden="true"></i>
                Dashboard
            </span>
            <h1 class="hero-title hero-title-tight">Bem-vindo ao GeoFlora</h1>
            <p class="hero-text">Portal de administracao, planeamento e monitorizacao botanica</p>

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
                'label' => 'Especies',
                'value' => (int) $speciesCount,
                'icon' => 'fas fa-leaf',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Observacoes',
                'value' => (int) $observationCount,
                'icon' => 'fas fa-binoculars',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Revisao Manual',
                'value' => (int) $manualReviewCount,
                'icon' => 'fas fa-user-check',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Publicacoes',
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

    <section class="mt-6">
        <div class="section-header mb-4">
            <h2 class="section-title">
                <i class="fas fa-cube" aria-hidden="true"></i>
                Modulos Disponiveis
            </h2>
            <p class="section-description">Acede aos diferentes modulos da plataforma</p>
        </div>

        <div class="modules-grid">
            <div class="module-card">
                <div class="module-icon species">
                    <i class="fas fa-leaf" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Especies</h3>
                    <p>Pesquisa por especie, familia e genero com hierarquia de leitura.</p>
                </div>
                <a href="<?= Url::to(['species/index']) ?>" class="module-link">
                    Explorar catalogo
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            <div class="module-card">
                <div class="module-icon observation">
                    <i class="fas fa-binoculars" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Observacoes</h3>
                    <p>Segue estados de sincronizacao, confianca, autoria e publicacoes.</p>
                </div>
                <a href="<?= Url::to(['observation/index']) ?>" class="module-link">
                    Ver observacoes
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            <?php if (Yii::$app->user->identity?->isAdmin()): ?>
                <div class="module-card">
                    <div class="module-icon publication">
                        <i class="fas fa-user-check" aria-hidden="true"></i>
                    </div>
                    <div class="module-content">
                        <h3>Revisao manual</h3>
                        <p>Completa as observacoes onde a planta foi detetada mas nao reconhecida automaticamente.</p>
                    </div>
                    <a href="<?= Url::to(['observation/index', 'status' => 'MANUAL_REVIEW']) ?>" class="module-link">
                        Abrir fila de revisao
                        <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            <?php endif; ?>

            <div class="module-card">
                <div class="module-icon publication">
                    <i class="fas fa-newspaper" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Publicacoes</h3>
                    <p>Cria, edita e publica conteudo editorial a partir das observacoes.</p>
                </div>
                <a href="<?= Url::to(['publication/index']) ?>" class="module-link">
                    Ver publicacoes
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

            <div class="module-card">
                <div class="module-icon map">
                    <i class="fas fa-map" aria-hidden="true"></i>
                </div>
                <div class="module-content">
                    <h3>Mapa Interativo</h3>
                    <p>Visualiza todas as observacoes e especies num mapa interativo.</p>
                </div>
                <a href="<?= Url::to(['map/index']) ?>" class="module-link">
                    Abrir mapa
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </div>

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
        </div>
    </section>
</div>
