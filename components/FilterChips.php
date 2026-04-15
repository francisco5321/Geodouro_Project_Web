<?php

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * FilterChips Widget
 * Displays filter chips for filtering content
 * 
 * Usage:
 * <?= FilterChips::widget([
 *     'chips' => [
 *         ['label' => 'Todas', 'url' => ['index', 'status' => null], 'active' => true],
 *         ['label' => 'Publicadas', 'url' => ['index', 'status' => 'published']],
 *         ['label' => 'Pendentes', 'url' => ['index', 'status' => 'pending']],
 *     ],
 * ]) ?>
 */
class FilterChips extends Widget
{
    public array $chips = [];
    public string $cssClass = '';

    public function run(): string
    {
        if (empty($this->chips)) {
            return '';
        }

        $classes = ['filter-chips'];
        if ($this->cssClass) {
            $classes[] = $this->cssClass;
        }

        $html = Html::beginTag('div', ['class' => implode(' ', $classes)]);

        foreach ($this->chips as $chip) {
            $label = $chip['label'] ?? 'Filtro';
            $url = $chip['url'] ?? '#';
            $active = $chip['active'] ?? false;
            $icon = $chip['icon'] ?? null;
            $chipClass = $chip['class'] ?? '';

            $chipClasses = ['filter-chip'];
            if ($active) {
                $chipClasses[] = 'is-active';
            }
            if ($chipClass) {
                $chipClasses[] = $chipClass;
            }

            $chipContent = '';
            if ($icon) {
                $chipContent .= Html::tag('i', '', ['class' => $icon]);
            }
            $chipContent .= Html::encode($label);

            $html .= Html::a(
                $chipContent,
                $url,
                ['class' => implode(' ', $chipClasses)]
            );
        }

        $html .= Html::endTag('div');

        return $html;
    }
}
