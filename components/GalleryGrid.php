<?php

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

/**
 * GalleryGrid Widget
 * Displays a grid of images with lightbox support
 * 
 * Usage:
 * <?= GalleryGrid::widget([
 *     'images' => [
 *         ['url' => '/images/photo1.jpg', 'thumb' => '/images/thumb1.jpg', 'alt' => 'Photo 1'],
 *         ['url' => '/images/photo2.jpg', 'thumb' => '/images/thumb2.jpg', 'alt' => 'Photo 2'],
 *     ],
 *     'cssClass' => 'custom-gallery',
 * ]) ?>
 */
class GalleryGrid extends Widget
{
    public array $images = [];
    public string $cssClass = '';
    public string $galleryName = 'gallery';
    public bool $enableLightbox = true;

    public function run(): string
    {
        if (empty($this->images)) {
            return '';
        }

        $classes = ['gallery-grid'];
        if ($this->cssClass) {
            $classes[] = $this->cssClass;
        }

        $html = Html::beginTag('div', ['class' => implode(' ', $classes)]);

        foreach ($this->images as $image) {
            $imageUrl = $image['url'] ?? '';
            $thumbUrl = $image['thumb'] ?? $imageUrl;
            $alt = $image['alt'] ?? 'Gallery image';
            $title = $image['title'] ?? '';

            if (!$imageUrl) {
                continue;
            }

            $itemAttrs = ['class' => 'gallery-item'];

            if ($this->enableLightbox) {
                $itemAttrs['data-lightbox'] = $this->galleryName;
                $itemAttrs['href'] = $imageUrl;
                if ($title) {
                    $itemAttrs['data-title'] = $title;
                }
                $html .= Html::beginTag('a', $itemAttrs);
            } else {
                $html .= Html::beginTag('div', $itemAttrs);
            }

            $html .= Html::img($thumbUrl, [
                'alt' => $alt,
                'class' => 'gallery-item-img'
            ]);

            if ($this->enableLightbox) {
                $html .= Html::beginTag('div', ['class' => 'gallery-item-overlay']);
                $html .= Html::tag('i', '', ['class' => 'fas fa-search-plus gallery-item-overlay-icon']);
                $html .= Html::endTag('div');
            }

            if ($this->enableLightbox) {
                $html .= Html::endTag('a');
            } else {
                $html .= Html::endTag('div');
            }
        }

        $html .= Html::endTag('div');

        return $html;
    }
}
