<?php

use app\models\Publication;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Publication $publication */

$this->title = $publication->title ?: 'Publicacao #' . $publication->publication_id;
?>
<div class="module-shell">
    <a class="back-link" href="<?= Url::to(['publication/index']) ?>">&larr; Voltar as publicacoes</a>

    <section class="species-detail-hero mb-4">
        <div class="species-detail-copy">
            <span class="eyebrow">Publicacao</span>
            <h1 class="hero-title hero-title-tight"><?= Html::encode($publication->title ?: 'Publicacao botanica') ?></h1>
            <p class="species-detail-scientific"><?= Html::encode($publication->plantSpecies?->scientific_name ?? $publication->observation?->getResolvedScientificName() ?? 'Sem especie associada') ?></p>
            <div class="species-meta-row">
                <span class="species-meta-chip"><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></span>
                <span class="species-meta-chip"><?= Html::encode(Yii::$app->formatter->asDatetime($publication->published_at, 'php:d/m/Y H:i')) ?></span>
                <span class="species-meta-chip"><?= count($publication->publicationImages) ?> imagens</span>
            </div>
            <p class="hero-text"><?= Html::encode($publication->description ?: 'Sem texto editorial associado a esta publicacao.') ?></p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>ID</span><strong>#<?= (int) $publication->publication_id ?></strong></article>
            <article class="detail-stat-card"><span>Observacao</span><strong>#<?= (int) $publication->observation_id ?></strong></article>
            <article class="detail-stat-card"><span>Autor</span><strong><?= Html::encode($publication->user?->getFullName() ?? 'Sistema') ?></strong></article>
            <article class="detail-stat-card"><span>Especie</span><strong><?= Html::encode($publication->plantSpecies?->common_name ?: 'N/D') ?></strong></article>
        </div>
    </section>

    <section class="detail-section">
        <div class="module-link-list">
            <a href="<?= Url::to(['observation/view', 'id' => $publication->observation_id]) ?>">Abrir observacao original</a>
            <?php if ($publication->plant_species_id): ?><a href="<?= Url::to(['species/view', 'id' => $publication->plant_species_id]) ?>">Abrir ficha da especie</a><?php endif; ?>
            <a href="<?= Url::to(['map/index']) ?>">Ver observacoes no mapa</a>
        </div>
    </section>
</div>
