<?php

use app\models\Observation;
use app\models\PlantSpecies;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var PlantSpecies $species */
/** @var Observation[] $observations */
/** @var array $stats */

$this->title = $species->scientific_name;
$avgConfidence = $stats['avgConfidence'] !== null ? round((float) $stats['avgConfidence'] * 100) : null;
?>
<div class="species-detail-shell">
    <a class="back-link" href="<?= Url::to(['species/index']) ?>">&larr; Voltar ao catalogo</a>

    <section class="species-detail-hero mb-4">
        <div class="species-detail-copy">
            <span class="eyebrow">Ficha botanica</span>
            <h1 class="hero-title hero-title-tight"><?= Html::encode($species->common_name ?: $species->scientific_name) ?></h1>
            <p class="species-detail-scientific"><?= Html::encode($species->scientific_name) ?></p>
            <div class="species-meta-row">
                <span class="species-meta-chip"><?= Html::encode($species->family) ?></span>
                <span class="species-meta-chip"><?= Html::encode($species->genus) ?></span>
                <span class="species-meta-chip"><?= Html::encode($species->species) ?></span>
            </div>
            <p class="hero-text">
                <?= Html::encode($species->description ?: 'Ainda nao existe descricao editorial para esta especie. A web ja esta preparada para enriquecer a ficha com conteudo taxonomico, observacoes e referencias cruzadas com o mobile.') ?>
            </p>
            <div class="hero-cta-row mt-4">
                <?= Html::beginForm(['visit/toggle-species', 'id' => $species->plant_species_id], 'post', ['class' => 'd-inline-block']) ?>
                    <?= Html::submitButton($species->isSavedForUser(Yii::$app->user->identity) ? 'Remover de Quero visitar' : 'Guardar em Quero visitar', ['class' => 'btn btn-outline-brand']) ?>
                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="detail-stat-grid">
            <article class="detail-stat-card">
                <span>Observacoes</span>
                <strong><?= (int) $stats['observationsCount'] ?></strong>
            </article>
            <article class="detail-stat-card">
                <span>Publicadas</span>
                <strong><?= (int) $stats['publishedCount'] ?></strong>
            </article>
            <article class="detail-stat-card">
                <span>Confianca media</span>
                <strong><?= $avgConfidence !== null ? $avgConfidence . '%' : 'N/D' ?></strong>
            </article>
            <article class="detail-stat-card">
                <span>Imagens</span>
                <strong><?= (int) $species->image_count ?></strong>
            </article>
        </div>
    </section>

    <section class="detail-section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">Observacoes recentes</span>
                <h2>Ultimos registos associados</h2>
            </div>
        </div>

        <?php if (empty($observations)): ?>
            <div class="empty-state-card">
                <h3>Sem observacoes associadas</h3>
                <p>Quando a app mobile sincronizar registos desta especie, eles vao aparecer aqui.</p>
            </div>
        <?php else: ?>
            <div class="observation-list">
                <?php foreach ($observations as $observation): ?>
                    <?php
                    $statusLabel = $observation->is_published
                        ? 'Publicada'
                        : ($observation->sync_status === Observation::SYNC_SYNCED ? 'Sincronizada' : ($observation->sync_status === Observation::SYNC_FAILED ? 'Falha de sincronizacao' : 'Pendente'));
                    ?>
                    <article class="observation-card-web">
                        <div class="observation-card-top">
                            <div>
                                <p class="observation-title"><?= Html::encode($observation->getResolvedCommonName() ?: 'Observacao botanica') ?></p>
                                <p class="observation-subtitle"><?= Html::encode($observation->getResolvedScientificName() ?: 'Sem classificacao enriquecida') ?></p>
                            </div>
                            <span class="status-pill<?= $observation->is_published ? ' is-published' : '' ?>">
                                <?= Html::encode($statusLabel) ?>
                            </span>
                        </div>
                        <div class="observation-meta-grid">
                            <div>
                                <span>Autor</span>
                                <strong><?= Html::encode($observation->user?->getFullName() ?? 'Sistema') ?></strong>
                            </div>
                            <div>
                                <span>Observada em</span>
                                <strong><?= Html::encode(Yii::$app->formatter->asDatetime($observation->observed_at, 'php:d/m/Y H:i')) ?></strong>
                            </div>
                            <div>
                                <span>Confianca</span>
                                <strong><?= $observation->confidence !== null ? (int) round($observation->confidence * 100) . '%' : 'N/D' ?></strong>
                            </div>
                            <div>
                                <span>Coordenadas</span>
                                <strong><?= $observation->hasCoordinates() ? Html::encode(number_format((float) $observation->latitude, 4) . ', ' . number_format((float) $observation->longitude, 4)) : 'Sem localizacao' ?></strong>
                            </div>
                        </div>
                        <?php if (!empty($observation->notes)): ?>
                            <p class="observation-notes"><?= Html::encode($observation->notes) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
