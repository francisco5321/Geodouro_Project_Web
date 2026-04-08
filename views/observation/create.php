<?php

use app\models\Observation;

/** @var yii\web\View $this */
/** @var Observation $model */
/** @var array $userOptions */
/** @var array $speciesOptions */

$this->title = 'Criar observacao';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['map/index']) ?>">&larr; Voltar ao mapa</a>
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Criacao manual</span>
            <h1 class="hero-title hero-title-tight">Nova observacao</h1>
            <p class="hero-text">Preenche os campos necessarios da observacao. Se vieste do mapa, as coordenadas ja entram pre-preenchidas.</p>
        </div>
    </section>

    <?= $this->render('_form', [
        'model' => $model,
        'userOptions' => $userOptions,
        'speciesOptions' => $speciesOptions,
    ]) ?>
</div>
