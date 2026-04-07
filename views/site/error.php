<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

$this->title = $name;
?>
<div class="content-card">
    <span class="eyebrow">Erro</span>
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="hero-text"><?= nl2br(Html::encode($message)) ?></p>
</div>
