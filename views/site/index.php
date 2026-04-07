<?php

/** @var yii\web\View $this */

$this->title = 'Dashboard';
?>
<div class="hero-panel mb-4">
    <div>
        <span class="eyebrow">GeoDouro x GeoFlora</span>
        <h1 class="hero-title">Portal de administracao e monitorizacao botanica</h1>
        <p class="hero-text">
            Esta base web usa a mesma base de dados do projeto mobile para consultar especies, observacoes, publicacoes e leitura geografica no mapa.
        </p>
    </div>
    <div class="hero-accent"></div>
</div>

<div class="row g-4">
    <div class="col-md-6 col-xl-3">
        <div class="metric-card">
            <span>Especies</span>
            <strong><?= (int) $speciesCount ?></strong>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="metric-card">
            <span>Observacoes</span>
            <strong><?= (int) $observationCount ?></strong>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="metric-card">
            <span>Publicacoes</span>
            <strong><?= (int) $publicationCount ?></strong>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="metric-card">
            <span>Utilizadores autenticados</span>
            <strong><?= (int) $userCount ?></strong>
        </div>
    </div>
</div>

<section class="module-hub mt-4">
    <article class="hub-card">
        <span class="eyebrow">Catalogo</span>
        <h2>Especies</h2>
        <p>Pesquisa por especie, familia e genero com a mesma hierarquia de leitura da app mobile.</p>
        <a href="<?= yii\helpers\Url::to(['species/index']) ?>">Abrir modulo</a>
    </article>
    <article class="hub-card">
        <span class="eyebrow">Operacional</span>
        <h2>Observacoes</h2>
        <p>Segue estados de sincronizacao, confianca, autoria e ligacao a especies e publicacoes.</p>
        <a href="<?= yii\helpers\Url::to(['observation/index']) ?>">Abrir modulo</a>
    </article>
    <article class="hub-card">
        <span class="eyebrow">Editorial</span>
        <h2>Publicacoes</h2>
        <p>Consulta a camada publica e editorial gerada a partir das observacoes confirmadas.</p>
        <a href="<?= yii\helpers\Url::to(['publication/index']) ?>">Abrir modulo</a>
    </article>
    <article class="hub-card">
        <span class="eyebrow">Territorio</span>
        <h2>Mapa Leaflet</h2>
        <p>Visualiza os registos com coordenadas num mapa navegavel, pronto para filtros futuros.</p>
        <a href="<?= yii\helpers\Url::to(['map/index']) ?>">Abrir modulo</a>
    </article>
</section>
