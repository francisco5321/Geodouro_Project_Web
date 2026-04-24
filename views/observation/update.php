<?php

use app\models\Observation;

/** @var yii\web\View $this */
/** @var Observation $model */
/** @var array $userOptions */
/** @var array $speciesOptions */
/** @var array $speciesData */

$this->title = 'Editar observacao';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['observation/view', 'id' => $model->observation_id]) ?>" title="Voltar a observacao">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Voltar a observacao
    </a>
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-edit" style="margin-right: 0.4em;" aria-hidden="true"></i>
                Gestao de observacoes
            </span>
            <h1 class="hero-title hero-title-tight">Editar observacao #<?= (int) $model->observation_id ?></h1>
            <p class="hero-text">Atualiza os dados da observacao e guarda as alteracoes quando terminares.</p>
        </div>
    </section>

    <?= $this->render('_form', [
        'model' => $model,
        'userOptions' => $userOptions,
        'speciesOptions' => $speciesOptions,
        'speciesData' => $speciesData,
    ]) ?>
</div>
