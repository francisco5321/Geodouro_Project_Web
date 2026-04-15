<?php

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * EmptyState Widget
 * Displays an empty state message with optional actions
 * 
 * Usage:
 * <?= EmptyState::widget([
 *     'icon' => 'fas fa-search',
 *     'title' => 'Nenhuma espécie encontrada',
 *     'message' => 'Tente ajustar sua pesquisa ou filtros.',
 *     'actions' => [
 *         ['label' => 'Voltar', 'url' => ['species/index'], 'class' => 'btn-outline-brand'],
 *         ['label' => 'Nova Espécie', 'url' => ['species/create'], 'class' => 'btn-brand'],
 *     ],
 * ]) ?>
 */
class EmptyState extends Widget
{
    public ?string $icon = null;
    public string $title = 'Nenhum resultado encontrado';
    public string $message = '';
    public array $actions = [];
    public string $cssClass = '';

    public function run(): string
    {
        $classes = ['empty-state'];
        if ($this->cssClass) {
            $classes[] = $this->cssClass;
        }

        $html = Html::beginTag('section', ['class' => implode(' ', $classes)]);

        if ($this->icon) {
            $html .= Html::tag('i', '', ['class' => 'empty-state-icon ' . $this->icon]);
        }

        $html .= Html::tag('h2', Html::encode($this->title), ['class' => 'empty-state-title']);

        if ($this->message) {
            $html .= Html::tag('p', Html::encode($this->message), ['class' => 'empty-state-message']);
        }

        if (!empty($this->actions)) {
            $html .= Html::beginTag('div', ['class' => 'empty-state-action']);

            foreach ($this->actions as $action) {
                $label = $action['label'] ?? 'Ação';
                $url = $action['url'] ?? '#';
                $btnClass = $action['class'] ?? 'btn-outline-brand';
                $icon = $action['icon'] ?? null;

                $btnContent = '';
                if ($icon) {
                    $btnContent .= Html::tag('i', '', ['class' => $icon . ' me-2']);
                }
                $btnContent .= Html::encode($label);

                $html .= Html::a(
                    $btnContent,
                    $url,
                    ['class' => 'btn ' . $btnClass]
                );
            }

            $html .= Html::endTag('div');
        }

        $html .= Html::endTag('section');

        return $html;
    }
}
