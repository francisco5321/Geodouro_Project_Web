<?php

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * StatCard Widget
 * Displays a statistic with label and optional icon
 * 
 * Usage:
 * <?= StatCard::widget([
 *     'label' => 'Total Espécies',
 *     'value' => 156,
 *     'icon' => 'fas fa-leaf',
 *     'subtext' => 'No ecossistema',
 * ]) ?>
 */
class StatCard extends Widget
{
    public string $label = '';
    public string|int $value = 0;
    public ?string $icon = null;
    public ?string $subtext = null;
    public string $cssClass = '';

    public function run(): string
    {
        $classes = ['stat-card'];
        if ($this->cssClass) {
            $classes[] = $this->cssClass;
        }

        $html = Html::beginTag('article', ['class' => implode(' ', $classes)]);

        if ($this->icon) {
            $html .= Html::tag('i', '', ['class' => 'stat-card-icon ' . $this->icon]);
        }

        $html .= Html::tag('span', Html::encode($this->label), ['class' => 'stat-card-label']);
        $html .= Html::tag('strong', Html::encode($this->value), ['class' => 'stat-card-value']);

        if ($this->subtext) {
            $html .= Html::tag('p', Html::encode($this->subtext), ['class' => 'stat-card-subtext']);
        }

        $html .= Html::endTag('article');

        return $html;
    }
}
