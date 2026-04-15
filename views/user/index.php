<?php

use app\components\StatCard;
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
            <span class="eyebrow">
                <i class="fas fa-shield-alt" aria-hidden="true"></i>
                Administração
            </span>
            <h1 class="hero-title hero-title-tight">Gestão de Utilizadores</h1>
            <p class="hero-text">Define quem fica com permissões de administração na web e acompanha a base de utilizadores do portal.</p>
        </div>
        <div class="detail-stat-grid">
            <?= StatCard::widget([
                'label' => 'Utilizadores',
                'value' => count($users),
                'icon' => 'fas fa-users',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Admins',
                'value' => count(array_filter($users, static fn($user) => $user->isAdmin())),
                'icon' => 'fas fa-crown',
            ]) ?>
            <?= StatCard::widget([
                'label' => 'Roles',
                'value' => $roleColumnAvailable ? 'Ativo' : 'Pendente',
                'icon' => 'fas fa-cogs',
            ]) ?>
        </div>
    </section>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert-success-custom mb-4">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <section class="catalog-toolbar mb-4">
        <div class="toolbar-header">
            <h2 class="section-title">
                <i class="fas fa-search" aria-hidden="true"></i>
                Filtrar Utilizadores
            </h2>
        </div>
        <div class="toolbar-body">
            <?= Html::beginForm(['user/index'], 'get', ['class' => 'user-search-form']) ?>
                <div class="user-search-input-wrap">
                    <?= Html::textInput('q', $search, ['class' => 'form-control user-search-input', 'placeholder' => 'Pesquisar por nome, username ou email']) ?>
                </div>
                <div class="toolbar-actions">
                    <?= Html::submitButton('Pesquisar', ['class' => 'btn btn-brand']) ?>
                    <?php if ($search !== ''): ?>
                        <a class="btn btn-outline" href="<?= Url::to(['user/index']) ?>">Limpar</a>
                    <?php endif; ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </section>

    <section class="content-card">
        <h2 class="section-title mb-4">
            <i class="fas fa-list" aria-hidden="true"></i>
            Lista de Utilizadores
        </h2>
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
