<?php

use app\models\Observation;

/** @var yii\web\View $this */
/** @var Observation $model */
/** @var array $userOptions */
/** @var array $speciesOptions */

$this->title = 'Criar observação';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['map/index']) ?>" title="Voltar ao mapa">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Voltar ao mapa
    </a>
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-plus-circle" style="margin-right: 0.4em;" aria-hidden="true"></i>
                Criação manual
            </span>
            <h1 class="hero-title hero-title-tight">Nova observação</h1>
            <p class="hero-text">Preenche os campos necessários da observação. Se vieste do mapa, as coordenadas já entram pré-preenchidas automaticamente.</p>
        </div>
    </section>

    <?= $this->render('_form', [
        'model' => $model,
        'userOptions' => $userOptions,
        'speciesOptions' => $speciesOptions,
    ]) ?>
</div>
