<?php

use app\models\AppUser;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var AppUser[] $users */
/** @var yii\data\Pagination $pagination */
/** @var bool $roleColumnAvailable */
/** @var string $search */

$this->title = 'Utilizadores';
?>
<div class="module-shell">
    <section class="species-hero mb-4">
        <div>
            <span class="eyebrow">Administracao</span>
            <h1 class="hero-title hero-title-tight">Gestao de utilizadores autenticados</h1>
            <p class="hero-text">Define quem fica com permissoes de administracao na web e acompanha a base de utilizadores do portal.</p>
        </div>
        <div class="detail-stat-grid">
            <article class="detail-stat-card"><span>Utilizadores</span><strong><?= count($users) ?></strong></article>
            <article class="detail-stat-card"><span>Role column</span><strong><?= $roleColumnAvailable ? 'Ativa' : 'Pendente' ?></strong></article>
            <article class="detail-stat-card"><span>Admins</span><strong><?= count(array_filter($users, static fn($user) => $user->isAdmin())) ?></strong></article>
            <article class="detail-stat-card"><span>Estado</span><strong>Operacional</strong></article>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-geoflora mb-4"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>

    <section class="toolbar-card mb-4">
        <div class="toolbar-row user-search-row">
            <?= Html::beginForm(['user/index'], 'get', ['class' => 'user-search-form']) ?>
                <div class="user-search-input-wrap">
                    <?= Html::textInput('q', $search, ['class' => 'form-control user-search-input', 'placeholder' => 'Pesquisar por nome, username ou email']) ?>
                </div>
                <div class="toolbar-actions">
                    <?= Html::submitButton('Pesquisar', ['class' => 'btn btn-brand']) ?>
                    <?php if ($search !== ''): ?>
                        <a class="btn btn-outline-brand" href="<?= Url::to(['user/index']) ?>">Limpar</a>
                    <?php endif; ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </section>

    <section class="content-card">
        <h2>Lista de utilizadores</h2>
        <div class="user-admin-table-wrap">
            <table class="user-admin-table">
                <thead>
                    <tr>
                        <th>Utilizador</th>
                        <th>Email</th>
                        <th>Papel</th>
                        <th>Criado</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state-card compact-empty-state mb-0">
                                    <h3>Sem resultados</h3>
                                    <p>Nao encontramos utilizadores para a pesquisa atual.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?= Html::encode($user->getFullName()) ?></strong>
                                <div class="table-subtext">@<?= Html::encode($user->username ?: 'sem-username') ?></div>
                            </td>
                            <td><?= Html::encode($user->email ?: 'Sem email') ?></td>
                            <td><span class="species-meta-chip"><?= Html::encode($user->getRoleLabel()) ?></span></td>
                            <td><?= Html::encode(Yii::$app->formatter->asDate($user->created_at, 'php:d/m/Y')) ?></td>
                            <td>
                                <div class="table-action-row">
                                    <?php if ($roleColumnAvailable && (int) $user->user_id !== (int) Yii::$app->user->id): ?>
                                        <?php if ($user->isAdmin()): ?>
                                            <?= Html::beginForm(['user/set-role', 'id' => $user->user_id, 'role' => AppUser::ROLE_USER], 'post') ?>
                                                <?= Html::hiddenInput('q', $search) ?>
                                                <?= Html::submitButton('Tornar utilizador', ['class' => 'btn btn-link table-action-button', 'data-confirm' => 'Tens a certeza que queres remover privilegios de administrador a este utilizador?']) ?>
                                            <?= Html::endForm() ?>
                                        <?php else: ?>
                                            <?= Html::beginForm(['user/set-role', 'id' => $user->user_id, 'role' => AppUser::ROLE_ADMIN], 'post') ?>
                                                <?= Html::hiddenInput('q', $search) ?>
                                                <?= Html::submitButton('Tornar admin', ['class' => 'btn btn-link table-action-button', 'data-confirm' => 'Tens a certeza que queres promover este utilizador a administrador?']) ?>
                                            <?= Html::endForm() ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="table-subtext">Sem acao</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="catalog-pagination">
        <?= LinkPager::widget([
            'pagination' => $pagination,
            'options' => ['class' => 'pagination justify-content-center mb-0'],
            'linkOptions' => ['class' => 'page-link'],
            'pageCssClass' => 'page-item',
            'prevPageCssClass' => 'page-item',
            'nextPageCssClass' => 'page-item',
            'disabledPageCssClass' => 'page-item disabled',
            'activePageCssClass' => 'page-item active',
        ]) ?>
    </div>
</div>
