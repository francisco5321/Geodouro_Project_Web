<?php

use app\models\SpeciesForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var SpeciesForm $model */

$this->title = 'Atualizar espécie';
?>
<div class="module-shell">
    <a class="back-link" href="<?= Url::to(['species/view', 'id' => $model->plant_species_id]) ?>">&larr; Voltar à espécie</a>

    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Administração</span>
            <h1 class="hero-title hero-title-tight">Atualizar espécie</h1>
            <p class="hero-text">Corrige os metadados botânicos apresentados no catálogo e nas observações associadas.</p>
        </div>
    </section>

    <?= $this->render('_form', ['model' => $model]) ?>
</div>
