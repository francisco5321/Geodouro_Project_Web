<?php

use app\models\Observation;
use app\models\PlantSpecies;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var PlantSpecies $species */
/** @var Observation[] $observations */
/** @var array $galleryImages */
/** @var string|null $locationSummary */
/** @var array $stats */

$this->title = $species->scientific_name;
$heroImage = $galleryImages[0] ?? null;
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;0,9..144,700;1,9..144,300;1,9..144,500&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

<div class="sd-shell">

    <!-- Back -->
    <a class="sd-back" href="<?= Url::to(['species/index']) ?>">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 12L6 8l4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Voltar ao catálogo
    </a>

    <!-- Hero -->
    <section class="sd-hero">
        <?php if ($heroImage !== null): ?>
        <div class="sd-hero-image-wrap">
            <a
                href="<?= Url::to(['media/observation-image', 'id' => $heroImage['observationId'], 'index' => $heroImage['imageIndex']]) ?>"
                target="_blank" rel="noopener"
                class="sd-hero-image-link"
            >
                <img
                    src="<?= Url::to(['media/observation-image', 'id' => $heroImage['observationId'], 'index' => $heroImage['imageIndex']]) ?>"
                    alt="<?= Html::encode($species->scientific_name) ?>"
                    class="sd-hero-img"
                >
                <div class="sd-hero-overlay"></div>
                <div class="sd-hero-badge">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><rect x="1" y="3" width="14" height="11" rx="2" stroke="currentColor" stroke-width="1.4"/><circle cx="5.5" cy="7.5" r="1.5" stroke="currentColor" stroke-width="1.2"/><path d="M1 11l4-3 3 2.5L12 6l3 5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <?= (int) $species->image_count ?> imagens
                </div>
            </a>
        </div>
        <?php endif; ?>

        <!-- Identity card -->
        <div class="sd-identity">
            <div class="sd-chips-row">
                <span class="sd-chip sd-chip--family"><?= Html::encode($species->family) ?></span>
                <span class="sd-chip sd-chip--genus"><?= Html::encode($species->genus) ?></span>
            </div>
            <h1 class="sd-sci-name"><?= Html::encode($species->scientific_name) ?></h1>
            <?php if ($species->common_name): ?>
                <p class="sd-common-name"><?= Html::encode($species->common_name) ?></p>
            <?php endif; ?>

            <!-- Stats band -->
            <div class="sd-stats-band">
                <div class="sd-stat">
                    <span class="sd-stat-val"><?= (int) $stats['observationsCount'] ?></span>
                    <span class="sd-stat-lbl">Observações</span>
                </div>
                <div class="sd-stat-divider"></div>
                <div class="sd-stat">
                    <span class="sd-stat-val"><?= (int) $species->image_count ?></span>
                    <span class="sd-stat-lbl">Imagens</span>
                </div>
                <div class="sd-stat-divider"></div>
                <div class="sd-stat">
                    <span class="sd-stat-val"><?= (int) $stats['syncedCount'] ?></span>
                    <span class="sd-stat-lbl">Sincronizadas</span>
                </div>
                <div class="sd-stat-divider"></div>
                <div class="sd-stat">
                    <span class="sd-stat-val"><?= (int) $stats['publishedCount'] ?></span>
                    <span class="sd-stat-lbl">Publicadas</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery strip -->
    <?php if (count($galleryImages) > 1): ?>
    <section class="sd-gallery-section">
        <div class="sd-section-label">
            <span class="sd-eyebrow">Galeria</span>
        </div>
        <div class="sd-gallery-strip">
            <?php foreach ($galleryImages as $i => $image): ?>
                <a
                    class="sd-gallery-thumb<?= $i === 0 ? ' sd-gallery-thumb--active' : '' ?>"
                    href="<?= Url::to(['media/observation-image', 'id' => $image['observationId'], 'index' => $image['imageIndex']]) ?>"
                    target="_blank" rel="noopener"
                >
                    <img
                        src="<?= Url::to(['media/observation-image', 'id' => $image['observationId'], 'index' => $image['imageIndex']]) ?>"
                        alt="Imagem <?= $i + 1 ?> de <?= Html::encode($species->scientific_name) ?>"
                        loading="lazy"
                    >
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Location -->
    <?php if ($locationSummary !== null): ?>
    <section class="sd-location-section">
        <div class="sd-location-card">
            <div class="sd-location-icon">
                <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M10 1C6.686 1 4 3.686 4 7c0 4.5 6 12 6 12s6-7.5 6-12c0-3.314-2.686-6-6-6z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><circle cx="10" cy="7" r="2" stroke="currentColor" stroke-width="1.4"/></svg>
            </div>
            <div class="sd-location-body">
                <span class="sd-eyebrow">Localização</span>
                <p class="sd-location-text"><?= Html::encode($locationSummary) ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Observations -->
    <section class="sd-obs-section">
        <div class="sd-section-header">
            <div>
                <span class="sd-eyebrow">Registos</span>
                <h2 class="sd-section-title">Observações recentes</h2>
            </div>
            <span class="sd-obs-count-pill"><?= count($observations) ?></span>
        </div>

        <?php if (empty($observations)): ?>
            <div class="sd-empty">
                <div class="sd-empty-icon">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none"><circle cx="14" cy="14" r="12" stroke="currentColor" stroke-width="1.5"/><path d="M14 9v5M14 17v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </div>
                <h3>Sem observações</h3>
                <p>Quando a app mobile sincronizar registos desta espécie, vão aparecer aqui.</p>
            </div>
        <?php else: ?>
            <div class="sd-obs-feed">
                <?php foreach ($observations as $idx => $observation): ?>
                    <?php
                    $isPublished = $observation->is_published;
                    $isSynced    = $observation->sync_status === Observation::SYNC_SYNCED;
                    $isFailed    = $observation->sync_status === Observation::SYNC_FAILED;
                    $statusLabel = $isPublished
                        ? 'Publicada'
                        : ($isSynced ? 'Sincronizada' : ($isFailed ? 'Falha' : 'Pendente'));
                    $statusMod   = $isPublished ? 'published' : ($isSynced ? 'synced' : ($isFailed ? 'failed' : 'pending'));
                    $thumbUrl    = !empty($observation->getImageGalleryPaths())
                        ? Url::to(['media/observation-image', 'id' => $observation->observation_id, 'index' => 0])
                        : null;
                    $confidence  = $observation->confidence !== null ? (int) round($observation->confidence * 100) : null;
                    $confClass   = $confidence !== null
                        ? ($confidence >= 80 ? 'high' : ($confidence >= 50 ? 'mid' : 'low'))
                        : 'na';
                    ?>
                    <a
                        class="sd-obs-card"
                        href="<?= Url::to(['observation/view', 'id' => $observation->observation_id]) ?>"
                        style="animation-delay: <?= $idx * 60 ?>ms"
                    >
                        <div class="sd-obs-thumb">
                            <?php if ($thumbUrl !== null): ?>
                                <img src="<?= $thumbUrl ?>" alt="Observação <?= (int) $observation->observation_id ?>" loading="lazy">
                            <?php else: ?>
                                <div class="sd-obs-thumb-placeholder">
                                    <svg width="22" height="22" viewBox="0 0 22 22" fill="none"><rect x="1" y="3" width="20" height="16" rx="3" stroke="currentColor" stroke-width="1.4"/><circle cx="7.5" cy="9.5" r="2" stroke="currentColor" stroke-width="1.2"/><path d="M1 16l5.5-4L11 15l4.5-5 5.5 6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="sd-obs-body">
                            <strong class="sd-obs-name"><?= Html::encode($observation->getResolvedCommonName() ?: 'Observação botânica') ?></strong>
                            <p class="sd-obs-date"><?= Html::encode(Yii::$app->formatter->asDatetime($observation->observed_at, 'php:d/m/Y · H:i')) ?></p>
                            <?php if ($observation->publication !== null): ?>
                                <p class="sd-obs-publisher"><?= Html::encode('Publicado por ' . ($observation->publication->user?->getFullName() ?? 'Sistema')) ?></p>
                            <?php endif; ?>

                            <div class="sd-obs-badges">
                                <span class="sd-badge sd-badge--conf sd-badge--conf-<?= $confClass ?>">
                                    <?= $confidence !== null ? $confidence . '%' : 'N/D' ?>
                                </span>
                                <span class="sd-badge sd-badge--status sd-badge--<?= $statusMod ?>">
                                    <?= Html::encode($statusLabel) ?>
                                </span>
                            </div>
                        </div>

                        <div class="sd-obs-arrow">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</div>

<style>
/* ─── Reset & root ──────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --brand:        #2a6041;
    --brand-light:  #3d7a55;
    --brand-pale:   #e8f4ed;
    --brand-faint:  #f3f9f5;
    --ink:          #1a2420;
    --ink-2:        #3b4d44;
    --ink-3:        #647468;
    --border:       rgba(42, 96, 65, 0.12);
    --border-mid:   rgba(42, 96, 65, 0.22);
    --surface:      #ffffff;
    --surface-soft: #f6faf7;
    --radius-sm:    10px;
    --radius-md:    16px;
    --radius-lg:    24px;
    --radius-xl:    32px;
    --shadow-sm:    0 1px 4px rgba(30,60,40,0.06), 0 4px 16px rgba(30,60,40,0.05);
    --shadow-md:    0 2px 8px rgba(30,60,40,0.07), 0 8px 28px rgba(30,60,40,0.08);
    --shadow-hover: 0 4px 16px rgba(30,60,40,0.1), 0 12px 40px rgba(30,60,40,0.1);
    --font-display: 'Fraunces', Georgia, serif;
    --font-body:    'DM Sans', system-ui, sans-serif;
}

/* ─── Shell ─────────────────────────────────────────────── */
.sd-shell {
    font-family: var(--font-body);
    color: var(--ink);
    display: grid;
    gap: 2rem;
    max-width: 860px;
    margin: 0 auto;
    padding: 1.5rem 1rem 4rem;
}

/* ─── Back link ─────────────────────────────────────────── */
.sd-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--brand);
    text-decoration: none;
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    border: 1px solid var(--border);
    background: var(--surface);
    transition: background 0.15s, border-color 0.15s, transform 0.15s;
    width: fit-content;
}
.sd-back:hover {
    background: var(--brand-pale);
    border-color: var(--border-mid);
    transform: translateX(-2px);
}

/* ─── Hero ──────────────────────────────────────────────── */
.sd-hero {
    display: grid;
    gap: 1.5rem;
}

.sd-hero-image-wrap {
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    position: relative;
}

.sd-hero-image-link {
    display: block;
    position: relative;
    overflow: hidden;
}

.sd-hero-img {
    width: 100%;
    height: 420px;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}

.sd-hero-image-link:hover .sd-hero-img { transform: scale(1.03); }

.sd-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent 40%, rgba(10, 30, 18, 0.55) 100%);
    pointer-events: none;
}

.sd-hero-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.4rem 0.9rem;
    border-radius: 999px;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: var(--brand);
    font-weight: 600;
    font-size: 0.82rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.12);
}

/* ─── Identity ──────────────────────────────────────────── */
.sd-identity {
    background: var(--surface);
    border-radius: var(--radius-xl);
    border: 1px solid var(--border);
    padding: 2rem;
    box-shadow: var(--shadow-sm);
    display: grid;
    gap: 0.75rem;
}

.sd-chips-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.sd-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.3rem 0.85rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.sd-chip--family {
    background: var(--brand-pale);
    color: var(--brand);
    border: 1px solid rgba(42,96,65,0.18);
}

.sd-chip--genus {
    background: var(--surface-soft);
    color: var(--ink-2);
    border: 1px solid var(--border);
}

.sd-sci-name {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(1.8rem, 4vw, 2.6rem);
    color: var(--ink);
    line-height: 1.2;
    letter-spacing: -0.01em;
}

.sd-common-name {
    font-size: 1.05rem;
    color: var(--ink-3);
    font-weight: 400;
}

/* ─── Stats band ────────────────────────────────────────── */
.sd-stats-band {
    display: flex;
    align-items: center;
    gap: 0;
    margin-top: 1rem;
    padding-top: 1.25rem;
    border-top: 1px solid var(--border);
    overflow-x: auto;
}

.sd-stat {
    flex: 1;
    min-width: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
    padding: 0.5rem;
}

.sd-stat-val {
    font-family: var(--font-display);
    font-size: 2rem;
    font-weight: 500;
    color: var(--brand);
    line-height: 1;
}

.sd-stat-lbl {
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--ink-3);
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.sd-stat-divider {
    width: 1px;
    height: 44px;
    background: var(--border);
    flex-shrink: 0;
}

/* ─── Gallery ───────────────────────────────────────────── */
.sd-gallery-section {
    display: grid;
    gap: 0.75rem;
}

.sd-gallery-strip {
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scrollbar-width: thin;
    scrollbar-color: var(--border) transparent;
}

.sd-gallery-strip::-webkit-scrollbar { height: 4px; }
.sd-gallery-strip::-webkit-scrollbar-track { background: transparent; }
.sd-gallery-strip::-webkit-scrollbar-thumb { background: var(--border-mid); border-radius: 4px; }

.sd-gallery-thumb {
    flex: 0 0 96px;
    width: 96px;
    height: 96px;
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 2px solid transparent;
    box-shadow: var(--shadow-sm);
    transition: border-color 0.18s, transform 0.18s, box-shadow 0.18s;
}

.sd-gallery-thumb:hover,
.sd-gallery-thumb--active {
    border-color: var(--brand);
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.sd-gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* ─── Location ──────────────────────────────────────────── */
.sd-location-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 1.25rem 1.5rem;
    box-shadow: var(--shadow-sm);
}

.sd-location-icon {
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-sm);
    background: var(--brand-pale);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
}

.sd-location-body { display: grid; gap: 0.3rem; }

.sd-location-text {
    font-size: 0.95rem;
    color: var(--ink-2);
    line-height: 1.55;
}

/* ─── Observations ──────────────────────────────────────── */
.sd-obs-section { display: grid; gap: 1.25rem; }

.sd-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.sd-section-title {
    font-family: var(--font-display);
    font-size: 1.5rem;
    font-weight: 500;
    color: var(--ink);
    margin-top: 0.2rem;
}

.sd-obs-count-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 28px;
    padding: 0 0.75rem;
    border-radius: 999px;
    background: var(--brand-pale);
    color: var(--brand);
    font-size: 0.82rem;
    font-weight: 700;
    border: 1px solid rgba(42,96,65,0.18);
}

/* ─── Eyebrow ───────────────────────────────────────────── */
.sd-eyebrow {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--brand-light);
}

/* ─── Empty state ───────────────────────────────────────── */
.sd-empty {
    background: var(--surface-soft);
    border: 1px dashed var(--border-mid);
    border-radius: var(--radius-lg);
    padding: 3rem 2rem;
    text-align: center;
    display: grid;
    justify-items: center;
    gap: 0.6rem;
}

.sd-empty-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--brand-pale);
    color: var(--brand);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.5rem;
}

.sd-empty h3 { font-size: 1.1rem; font-weight: 600; color: var(--ink); }
.sd-empty p  { font-size: 0.9rem; color: var(--ink-3); max-width: 32ch; line-height: 1.55; }

/* ─── Observation feed ──────────────────────────────────── */
.sd-obs-feed {
    display: grid;
    gap: 0.75rem;
}

.sd-obs-card {
    display: grid;
    grid-template-columns: 80px minmax(0,1fr) 20px;
    gap: 1rem;
    align-items: center;
    padding: 1rem 1.25rem 1rem 1rem;
    border-radius: var(--radius-lg);
    background: var(--surface);
    border: 1px solid var(--border);
    box-shadow: var(--shadow-sm);
    text-decoration: none;
    color: inherit;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    animation: sd-fadein 0.4s both;
}

@keyframes sd-fadein {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.sd-obs-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
    border-color: var(--border-mid);
}

.sd-obs-thumb {
    width: 80px;
    height: 80px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    flex-shrink: 0;
    background: var(--surface-soft);
}

.sd-obs-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.sd-obs-thumb-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-3);
    background: var(--brand-faint);
}

.sd-obs-body  { display: grid; gap: 0.2rem; min-width: 0; }
.sd-obs-name  { font-size: 0.98rem; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sd-obs-date  { font-size: 0.82rem; color: var(--ink-3); }
.sd-obs-publisher { font-size: 0.8rem; color: var(--ink-3); margin-top: 0.1rem; }

.sd-obs-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-top: 0.4rem;
}

.sd-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1;
}

/* Confidence */
.sd-badge--conf { border: 1px solid transparent; }
.sd-badge--conf-high { background: #dff1e4; color: #1b6b3a; border-color: rgba(27,107,58,0.2); }
.sd-badge--conf-mid  { background: #fef5e0; color: #8a6200; border-color: rgba(138,98,0,0.18); }
.sd-badge--conf-low  { background: #fee8e7; color: #972322; border-color: rgba(151,35,34,0.18); }
.sd-badge--conf-na   { background: var(--surface-soft); color: var(--ink-3); border-color: var(--border); }

/* Status */
.sd-badge--status     { background: var(--surface-soft); color: var(--ink-3); border: 1px solid var(--border); }
.sd-badge--published  { background: #dff1e4; color: #1b6b3a; border-color: rgba(27,107,58,0.2); }
.sd-badge--synced     { background: #e0eeff; color: #1a4fa3; border-color: rgba(26,79,163,0.2); }
.sd-badge--failed     { background: #fee8e7; color: #972322; border-color: rgba(151,35,34,0.18); }
.sd-badge--pending    { background: #fef5e0; color: #8a6200; border-color: rgba(138,98,0,0.18); }

.sd-obs-arrow { color: var(--ink-3); display: flex; align-items: center; justify-content: flex-end; }

/* ─── Responsive ────────────────────────────────────────── */
@media (max-width: 600px) {
    .sd-shell { gap: 1.25rem; padding: 1rem 0.75rem 3rem; }

    .sd-hero-img { height: 280px; }

    .sd-identity { padding: 1.25rem; gap: 0.6rem; }

    .sd-sci-name { font-size: 1.6rem; }

    .sd-stat-val { font-size: 1.5rem; }

    .sd-stats-band { overflow-x: auto; }

    .sd-obs-card {
        grid-template-columns: 68px minmax(0,1fr) 16px;
        padding: 0.85rem 1rem 0.85rem 0.75rem;
        gap: 0.75rem;
    }

    .sd-obs-thumb { width: 68px; height: 68px; }
    .sd-obs-name  { font-size: 0.92rem; }
}

@media (min-width: 680px) {
    .sd-hero {
        grid-template-columns: 1fr 340px;
        align-items: start;
    }

    .sd-hero-image-wrap { grid-row: 1; }
    .sd-identity { grid-row: 1; }
}
</style>