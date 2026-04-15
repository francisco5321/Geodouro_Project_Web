<?php

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * TimelineCard Widget
 * Displays an item in a timeline/list format
 * 
 * Usage:
 * <?= TimelineCard::widget([
 *     'title' => 'Rosa Canina',
 *     'subtitle' => 'Rosa canina subsp. canina',
 *     'badge' => 'Publicada',
 *     'badgeClass' => 'is-published',
 *     'meta' => [
 *         'Autor' => 'João Silva',
 *         'Data' => '13/04/2026',
 *         'Confiança' => '92%',
 *     ],
 *     'actions' => [
 *         ['label' => 'Ver detalhes', 'url' => ['observation/view', 'id' => 1]],
 *         ['label' => 'Editar', 'url' => ['observation/update', 'id' => 1]],
 *     ],
 *     'content' => 'Observação adicional...',
 * ]) ?>
 */
class TimelineCard extends Widget
{
    public string $title = '';
    public ?string $subtitle = null;
    public ?string $badge = null;
    public string $badgeClass = '';
    public array $meta = [];
    public array $actions = [];
    public ?string $content = null;
    public string $cssClass = '';

    public function run(): string
    {
        $classes = ['timeline-item'];
        if ($this->cssClass) {
            $classes[] = $this->cssClass;
        }

        $html = Html::beginTag('article', ['class' => implode(' ', $classes)]);

        // Header with title and badge
        $html .= Html::beginTag('div', ['class' => 'timeline-item-header']);
        $html .= Html::beginTag('div');
        $html .= Html::tag('h3', Html::encode($this->title), ['class' => 'timeline-item-title']);
        if ($this->subtitle) {
            $html .= Html::tag('p', Html::encode($this->subtitle), ['class' => 'timeline-item-subtitle']);
        }
        $html .= Html::endTag('div');

        if ($this->badge) {
            $badgeClasses = ['timeline-item-badge'];
            if ($this->badgeClass) {
                $badgeClasses[] = $this->badgeClass;
            }
            $html .= Html::tag('span', Html::encode($this->badge), ['class' => implode(' ', $badgeClasses)]);
        }

        $html .= Html::endTag('div');

        // Meta information
        if (!empty($this->meta)) {
            $html .= Html::beginTag('div', ['class' => 'timeline-item-meta']);
            foreach ($this->meta as $label => $value) {
                $html .= Html::beginTag('div', ['class' => 'timeline-item-meta-item']);
                $html .= Html::tag('span', Html::encode($label), ['class' => 'timeline-item-meta-label']);
                $html .= Html::tag('strong', Html::encode($value), ['class' => 'timeline-item-meta-value']);
                $html .= Html::endTag('div');
            }
            $html .= Html::endTag('div');
        }

        // Content
        if ($this->content) {
            $html .= Html::tag('div', $this->content, ['class' => 'timeline-item-content']);
        }

        // Actions
        if (!empty($this->actions)) {
            $html .= Html::beginTag('div', ['class' => 'timeline-item-actions']);
            foreach ($this->actions as $action) {
                $label = $action['label'] ?? 'Link';
                $url = $action['url'] ?? '#';
                $icon = $action['icon'] ?? null;

                $linkContent = '';
                if ($icon) {
                    $linkContent .= Html::tag('i', '', ['class' => 'icon-label ' . $icon]);
                }
                $linkContent .= Html::encode($label);

                $html .= Html::a(
                    $linkContent,
                    $url,
                    ['class' => 'timeline-item-action-link']
                );
            }
            $html .= Html::endTag('div');
        }

        $html .= Html::endTag('article');

        return $html;
    }
}
