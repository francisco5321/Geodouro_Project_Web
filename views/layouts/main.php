<?php

use app\assets\AppAsset;
use yii\bootstrap5\Html;
use yii\helpers\Url;

AppAsset::register($this);

$geodouroEmail = 'geodouro@geodouro.pt';
$geodouroPhone = '+351 254 851 965';
$supportHours = 'Segunda a Sexta, 09:00-18:00';
$faviconHref = Url::to('@web/favicon.svg?v=2');
$this->registerLinkTag([
    'rel' => 'icon',
    'type' => 'image/svg+xml',
    'href' => $faviconHref,
]);
$this->registerLinkTag([
    'rel' => 'shortcut icon',
    'type' => 'image/svg+xml',
    'href' => $faviconHref,
]);

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
                                [
                                    'class' => 'dropdown-item logout-item',
                                    'onclick' => "event.preventDefault(); const logoutForm = this.form; Notification.confirm('Deseja terminar a sessão?', () => logoutForm.submit(), 'Sair da conta', { confirmButtonText: 'Sair' }); return false;"
                                ]
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

        <?php if (Yii::$app->session->hasFlash('warning')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Notification.warning('<?= addslashes(Yii::$app->session->getFlash('warning')) ?>');
                });
            </script>
        <?php endif; ?>
        
        <?= $content ?>
    </div>
</main>

<footer class="site-footer" aria-label="Rodapé do site">
    <div class="container-fluid app-content-wrap">
        <div class="site-footer-grid">
            <section class="footer-block footer-brand-block">
                <div class="footer-logo">
                    <span class="footer-logo-icon"><i class="fas fa-leaf" aria-hidden="true"></i></span>
                    <div>
                        <strong>GeoFlora</strong>
                        <small>Portal</small>
                    </div>
                </div>
                <span class="footer-kicker">Sobre o projeto</span>
                <p class="footer-brand-text">O projeto GeoFlora foi desenvolvido por Francisco Vitorino, aluno do Instituto Politécnico de Viseu, no âmbito do projeto de estágio realizado em colaboração com a Geodouro.</p>
                <div class="footer-social-row">
                    <span class="footer-social-badge" aria-hidden="true"><i class="fas fa-envelope"></i></span>
                    <span class="footer-social-badge" aria-hidden="true"><i class="fas fa-phone-alt"></i></span>
                    <span class="footer-social-badge" aria-hidden="true"><i class="fas fa-map-marked-alt"></i></span>
                    <span class="footer-social-badge" aria-hidden="true"><i class="fas fa-route"></i></span>
                </div>
            </section>

            <section class="footer-block">
                <span class="footer-kicker">Contactos</span>
                <h2>Fala com a administração</h2>
                <p>Suporte técnico, ajuda com acessos e questões sobre o conteúdo da plataforma.</p>
                <div class="footer-contact-stack">
                    <a href="mailto:<?= Html::encode($geodouroEmail) ?>" class="footer-contact-link">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        <span><?= Html::encode($geodouroEmail) ?></span>
                    </a>
                    <a href="tel:<?= Html::encode(str_replace(' ', '', $geodouroPhone)) ?>" class="footer-contact-link">
                        <i class="fas fa-phone-alt" aria-hidden="true"></i>
                        <span><?= Html::encode($geodouroPhone) ?></span>
                    </a>
                </div>
                <p class="footer-meta">Horário de apoio: <?= Html::encode($supportHours) ?></p>
            </section>

            <section class="footer-block">
                <span class="footer-kicker">Links Úteis</span>
                <ul class="footer-link-list">
                    <li><a href="<?= Url::to(['site/index']) ?>"><i class="fas fa-home" aria-hidden="true"></i><span>Dashboard</span></a></li>
                    <li><a href="<?= Url::to(['map/index']) ?>"><i class="fas fa-map" aria-hidden="true"></i><span>Mapa interativo</span></a></li>
                    <li><a href="<?= Url::to(['route-plan/index']) ?>"><i class="fas fa-route" aria-hidden="true"></i><span>Percursos</span></a></li>
                </ul>
            </section>

            <section class="footer-block">
                <span class="footer-kicker">Atualizações</span>
                <h2>Acompanha o portal</h2>
                <p>Consulta o mapa e constrói percursos antes de ires para o terreno.</p>
                <div class="footer-pill-row">
                    <span class="footer-pill">
                        <i class="fas fa-life-ring" aria-hidden="true"></i>
                        Suporte ao utilizador
                    </span>
                    <span class="footer-pill">
                        <i class="fas fa-seedling" aria-hidden="true"></i>
                        Planeamento botânico
                    </span>
                </div>
                <div class="footer-note">
                    <i class="fas fa-leaf" aria-hidden="true"></i>
                    <p class="footer-meta">Portal GeoFlora para gestão de espécies, observações, publicações e planeamento de visitas.</p>
                </div>
            </section>
        </div>
        <div class="site-footer-bottom">
            <div class="footer-brand-mark">
                <i class="fas fa-leaf" aria-hidden="true"></i>
                <span>GeoFlora</span>
            </div>
            <p class="footer-bottom-text">Planeamento botânico, exploração no território e apoio à visita num só lugar.</p>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
