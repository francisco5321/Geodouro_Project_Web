<?php

use app\models\PublicationForm;

/** @var yii\web\View $this */
/** @var PublicationForm $model */
/** @var array $observationOptions */
/** @var array $speciesOptions */

$this->title = 'Nova publicação';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['publication/index']) ?>">&larr; Voltar às publicações</a>

    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Workflow editorial</span>
            <h1 class="hero-title hero-title-tight">Criar nova publicação</h1>
            <p class="hero-text">Transforma uma observação validada num conteúdo editorial que o teu utilizador pode continuar a editar e publicar.</p>
        </div>
    </section>

    <?= $this->render('_form', [
        'model' => $model,
        'observationOptions' => $observationOptions,
        'speciesOptions' => $speciesOptions,
    ]) ?>
</div>
