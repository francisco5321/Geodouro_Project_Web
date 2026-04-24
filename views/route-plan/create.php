<?php

/** @var yii\web\View $this */
/** @var app\models\RoutePlan $model */

$this->title = 'Novo percurso';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['route-plan/index']) ?>">&larr; Voltar aos percursos</a>
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Planeamento</span>
            <h1 class="hero-title hero-title-tight">Criar percurso</h1>
            <p class="hero-text">Dá um nome ao percurso e depois adiciona as paragens no mapa do detalhe.</p>
        </div>
    </section>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
