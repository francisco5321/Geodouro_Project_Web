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
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title ?: Yii::$app->name) ?></title>
    <?php $this->head() ?>
</head>
<body class="app-shell">
<?php $this->beginBody() ?>
<header class="topbar">
    <div class="container-fluid topbar-inner">
        <a class="brand" href="<?= Yii::$app->homeUrl ?>">
            <span class="brand-mark"></span>
            <span>
                <strong>GeoFlora</strong>
                <small>Portal Web</small>
            </span>
        </a>
        <nav class="topbar-nav">
            <a href="<?= Yii::$app->homeUrl ?>">Dashboard</a>
            <a href="<?= yii\helpers\Url::to(['species/index']) ?>">Especies</a>
            <a href="<?= yii\helpers\Url::to(['observation/index']) ?>">Observacoes</a>
            <a href="<?= yii\helpers\Url::to(['publication/index']) ?>">Publicacoes</a>
            <a href="<?= yii\helpers\Url::to(['map/index']) ?>">Mapa</a>
            <?php if (Yii::$app->user->isGuest): ?>
                <a class="btn btn-brand" href="<?= yii\helpers\Url::to(['site/login']) ?>">Entrar</a>
            <?php else: ?>
                <span class="user-badge"><?= Html::encode(Yii::$app->user->identity->getFullName()) ?></span>
                <?= Html::beginForm(['site/logout'], 'post') ?>
                    <?= Html::submitButton('Sair', ['class' => 'btn btn-outline-brand']) ?>
                <?= Html::endForm() ?>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="main-content">
    <div class="container-fluid py-4 app-content-wrap">
        <?= $content ?>
    </div>
</main>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
