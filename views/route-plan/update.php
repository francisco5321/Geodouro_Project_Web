<?php

/** @var yii\web\View $this */
/** @var app\models\RoutePlan $model */

$this->title = 'Editar percurso';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['route-plan/view', 'id' => $model->route_plan_id]) ?>">&larr; Voltar ao percurso</a>
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Planeamento</span>
            <h1 class="hero-title hero-title-tight">Editar percurso</h1>
            <p class="hero-text">Atualiza o nome, a descrição e o ponto de partida do percurso sem perder as paragens que já definiste.</p>
        </div>
    </section>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>
