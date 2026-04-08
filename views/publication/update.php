<?php

use app\models\Publication;

/** @var yii\web\View $this */
/** @var Publication $model */
/** @var array $observationOptions */
/** @var array $speciesOptions */

$this->title = 'Editar publicacao';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['publication/view', 'id' => $model->publication_id]) ?>">&larr; Voltar a publicacao</a>

    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Edicao editorial</span>
            <h1 class="hero-title hero-title-tight">Atualizar publicacao #<?= (int) $model->publication_id ?></h1>
            <p class="hero-text">Administra o conteudo, o estado editorial e a ligacao desta publicacao a observacoes e especies.</p>
        </div>
    </section>

    <?= $this->render('_form', [
        'model' => $model,
        'observationOptions' => $observationOptions,
        'speciesOptions' => $speciesOptions,
    ]) ?>
</div>
