<?php

use app\models\Publication;

/** @var yii\web\View $this */
/** @var Publication $model */
/** @var array $observationOptions */
/** @var array $speciesOptions */

$this->title = 'Nova publicacao';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['publication/index']) ?>">&larr; Voltar as publicacoes</a>

    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Workflow editorial</span>
            <h1 class="hero-title hero-title-tight">Criar nova publicacao</h1>
            <p class="hero-text">Transforma uma observacao validada num conteudo editorial que o teu utilizador pode continuar a editar e publicar.</p>
        </div>
    </section>

    <?= $this->render('_form', [
        'model' => $model,
        'observationOptions' => $observationOptions,
        'speciesOptions' => $speciesOptions,
    ]) ?>
</div>
