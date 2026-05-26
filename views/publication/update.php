<?php

use app\models\PublicationForm;

/** @var yii\web\View $this */
/** @var PublicationForm $model */
/** @var array $observationOptions */
/** @var array $speciesOptions */

$this->title = 'Editar publicação';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['publication/view', 'id' => $model->publication_id]) ?>">&larr; Voltar à publicação</a>

    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Edição editorial</span>
            <h1 class="hero-title hero-title-tight">Atualizar publicação #<?= (int) $model->publication_id ?></h1>
            <p class="hero-text">Administra o conteúdo, o estado editorial e a ligação desta publicação a observações e espécies.</p>
        </div>
    </section>

    <?= $this->render('_form', [
        'model' => $model,
        'observationOptions' => $observationOptions,
        'speciesOptions' => $speciesOptions,
    ]) ?>
</div>
