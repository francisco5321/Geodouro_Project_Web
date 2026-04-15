<?php

use app\assets\AppAsset;
use yii\bootstrap5\Html;

AppAsset::register($this);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= Yii::$app->request->csrfToken ?>">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title ?: Yii::$app->name) ?></title>
    <?php $this->head() ?>
</head>
<body class="app-shell">
<?php $this->beginBody() ?>

<!-- Skip to main content link for accessibility -->
<a href="#main-content" class="skip-to-main">Ir para conteúdo principal</a>

<header class="topbar">
    <div class="container-fluid topbar-inner">
        <!-- Logo/Brand -->
        <a class="brand" href="<?= Yii::$app->homeUrl ?>" aria-label="GeoFlora Home">
            <div class="brand-icon">
                <i class="fas fa-leaf" aria-hidden="true"></i>
            </div>
            <div class="brand-text">
                <strong>GeoFlora</strong>
                <small>Portal</small>
            </div>
        </a>

        <!-- Navigation Menu -->
        <nav class="topbar-nav" role="navigation" aria-label="Menu Principal">
            <?php if (!Yii::$app->user->isGuest): ?>
                <div class="nav-section main-nav">
                    <a href="<?= Yii::$app->homeUrl ?>" 
                       class="nav-link<?= Yii::$app->controller->id === 'site' && Yii::$app->controller->action->id === 'index' ? ' is-active' : '' ?>"
                       title="Dashboard">
                        <i class="fas fa-home" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= yii\helpers\Url::to(['species/index']) ?>" 
                       class="nav-link<?= Yii::$app->controller->id === 'species' ? ' is-active' : '' ?>"
                       title="Catálogo de Espécies">
                        <i class="fas fa-leaf" aria-hidden="true"></i>
                        <span>Espécies</span>
                    </a>
                    <a href="<?= yii\helpers\Url::to(['observation/index']) ?>" 
                       class="nav-link<?= Yii::$app->controller->id === 'observation' ? ' is-active' : '' ?>"
                       title="Observações">
                        <i class="fas fa-binoculars" aria-hidden="true"></i>
                        <span>Observações</span>
                    </a>
                    <a href="<?= yii\helpers\Url::to(['publication/index']) ?>" 
                       class="nav-link<?= Yii::$app->controller->id === 'publication' ? ' is-active' : '' ?>"
                       title="Publicações">
                        <i class="fas fa-newspaper" aria-hidden="true"></i>
                        <span>Publicações</span>
                    </a>
                    <a href="<?= yii\helpers\Url::to(['map/index']) ?>" 
                       class="nav-link<?= Yii::$app->controller->id === 'map' ? ' is-active' : '' ?>"
                       title="Mapa Interativo">
                        <i class="fas fa-map" aria-hidden="true"></i>
                        <span>Mapa</span>
                    </a>
                </div>

                <div class="nav-divider"></div>

                <div class="nav-section secondary-nav">
                    <a href="<?= yii\helpers\Url::to(['visit/index']) ?>" 
                       class="nav-link<?= Yii::$app->controller->id === 'visit' ? ' is-active' : '' ?>"
                       title="Quero Visitar">
                        <i class="fas fa-heart" aria-hidden="true"></i>
                        <span>Visitas</span>
                    </a>
                    <a href="<?= yii\helpers\Url::to(['route-plan/index']) ?>" 
                       class="nav-link<?= Yii::$app->controller->id === 'route-plan' ? ' is-active' : '' ?>"
                       title="Planeamento de Percursos">
                        <i class="fas fa-route" aria-hidden="true"></i>
                        <span>Percursos</span>
                    </a>
                    <?php if (Yii::$app->user->identity?->isAdmin()): ?>
                        <a href="<?= yii\helpers\Url::to(['user/index']) ?>" 
                           class="nav-link admin-link<?= Yii::$app->controller->id === 'user' ? ' is-active' : '' ?>"
                           title="Gerenciar Utilizadores">
                            <i class="fas fa-shield-alt" aria-hidden="true"></i>
                            <span>Admin</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Guest Menu -->
                <div class="nav-section">
                    <span class="nav-text">Bem-vindo ao GeoFlora</span>
                </div>
            <?php endif; ?>
        </nav>

        <!-- User Menu -->
        <div class="topbar-user">
            <?php if (Yii::$app->user->isGuest): ?>
                <a class="btn-nav btn-nav-secondary" href="<?= yii\helpers\Url::to(['site/signup']) ?>" title="Criar Conta">
                    <i class="fas fa-user-plus" aria-hidden="true"></i>
                    <span>Registar</span>
                </a>
                <a class="btn-nav btn-nav-primary" href="<?= yii\helpers\Url::to(['site/login']) ?>" title="Entrar">
                    <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
                    <span>Entrar</span>
                </a>
            <?php else: ?>
                <div class="user-dropdown">
                    <button class="user-button" aria-haspopup="menu" aria-label="Menu do utilizador">
                        <i class="fas fa-user-circle" aria-hidden="true"></i>
                        <span class="user-name d-none d-lg-inline"><?= Html::encode(Yii::$app->user->identity->getFullName()) ?></span>
                        <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="user-dropdown-menu">
                        <a href="<?= yii\helpers\Url::to(['site/account']) ?>" class="dropdown-item">
                            <i class="fas fa-user" aria-hidden="true"></i>
                            Minha Conta
                        </a>
                        <a href="<?= yii\helpers\Url::to(['observation/index', 'my' => 1]) ?>" class="dropdown-item">
                            <i class="fas fa-binoculars" aria-hidden="true"></i>
                            Minhas Observações
                        </a>
                        <div class="dropdown-divider"></div>
                        <?= Html::beginForm(['site/logout'], 'post', ['class' => 'd-inline']) ?>
                            <?= Html::submitButton(
                                '<i class="fas fa-sign-out-alt" aria-hidden="true"></i> Sair',
                                ['class' => 'dropdown-item logout-item', 'onclick' => 'event.preventDefault(); if(confirm("Deseja sair?")) { this.form.submit(); }']
                            ) ?>
                        <?= Html::endForm() ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="main-content" id="main-content" role="main">
    <div class="container-fluid py-4 app-content-wrap">
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Notification.success('<?= addslashes(Yii::$app->session->getFlash('success')) ?>');
                });
            </script>
        <?php endif; ?>
        
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Notification.error('<?= addslashes(Yii::$app->session->getFlash('error')) ?>');
                });
            </script>
        <?php endif; ?>
        
        <?= $content ?>
    </div>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
