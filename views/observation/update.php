<?php

use app\models\ObservationForm;

/** @var yii\web\View $this */
/** @var ObservationForm $model */
/** @var array $userOptions */
/** @var array $speciesOptions */

$this->title = 'Editar observação';
?>
<div class="module-shell">
    <a class="back-link" href="<?= yii\helpers\Url::to(['observation/view', 'id' => $model->observation_id]) ?>" title="Voltar a observação">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Voltar a observação
    </a>
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">
                <i class="fas fa-edit" style="margin-right: 0.4em;" aria-hidden="true"></i>
                Gestão de observações
            </span>
            <h1 class="hero-title hero-title-tight">Editar observação #<?= (int) $model->observation_id ?></h1>
            <p class="hero-text">Atualiza os dados da observação e guarda as alterações quando terminares.</p>
        </div>
    </section>

    <?= $this->render('_form', [
        'model' => $model,
        'userOptions' => $userOptions,
        'speciesOptions' => $speciesOptions,
    ]) ?>
</div>
