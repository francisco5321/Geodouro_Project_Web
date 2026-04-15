<?php

namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;
use yii\widgets\ActiveField;

/**
 * FileUploadField Widget
 * Enhanced file upload with drag-and-drop and preview
 * 
 * Usage in view:
 * <?= FileUploadField::widget([
 *     'name' => 'image',
 *     'label' => 'Selecionar imagem',
 *     'accept' => 'image/*',
 *     'maxFiles' => 1,
 *     'showPreview' => true,
 * ]) ?>
 * 
 * Usage in form:
 * <?= $form->field($model, 'image_field')->widget(FileUploadField::class, [
 *     'accept' => 'image/*',
 *     'showPreview' => true,
 * ]) ?>
 */
class FileUploadField extends Widget
{
    public string $name = 'file';
    public string $label = 'Arrastar arquivo ou clicar para selecionar';
    public string $subtext = 'PNG, JPG, GIF até 10MB';
    public string $accept = '';
    public int $maxFiles = 1;
    public bool $showPreview = true;
    public ?string $icon = null;
    public string $cssClass = '';

    public function run(): string
    {
        if (!$this->icon) {
            $this->icon = 'fas fa-cloud-upload-alt';
        }

        $zoneId = 'upload-zone-' . substr(md5($this->name), 0, 8);
        $inputId = 'file-input-' . substr(md5($this->name), 0, 8);

        $classes = ['file-upload-zone'];
        if ($this->cssClass) {
            $classes[] = $this->cssClass;
        }

        $html = Html::beginTag('div', [
            'id' => $zoneId,
            'class' => implode(' ', $classes),
            'data-file-upload' => true,
        ]);

        // Hidden file input
        $html .= Html::fileInput($this->name, null, [
            'id' => $inputId,
            'class' => 'file-upload-input',
            'accept' => $this->accept,
            'multiple' => $this->maxFiles > 1,
        ]);

        // Upload zone content
        $html .= Html::beginTag('div', ['class' => 'file-upload-zone-content']);

        $html .= Html::tag('i', '', ['class' => 'file-upload-icon ' . $this->icon]);
        $html .= Html::tag('p', Html::encode($this->label), ['class' => 'file-upload-text']);
        $html .= Html::tag('p', Html::encode($this->subtext), ['class' => 'file-upload-subtext']);

        $html .= Html::endTag('div');

        // Preview container
        if ($this->showPreview) {
            $html .= Html::beginTag('div', ['class' => 'file-preview', 'id' => $inputId . '-preview']);
            $html .= Html::endTag('div');
        }

        $html .= Html::endTag('div');

        // JavaScript initialization
        $js = <<<JS
        document.addEventListener('DOMContentLoaded', function() {
            const zone = document.getElementById('$zoneId');
            const input = document.getElementById('$inputId');
            const preview = document.getElementById('$inputId-preview');
            
            if (UIHelpers && UIHelpers.setupFileUpload) {
                UIHelpers.setupFileUpload(zone, input, function(files) {
                    if (preview && $this->showPreview) {
                        preview.innerHTML = '';
                        Array.from(files).forEach((file, index) => {
                            if (index >= $this->maxFiles) return;
                            
                            const reader = new FileReader();
                            const item = document.createElement('div');
                            item.className = 'file-preview-item';
                            
                            reader.onload = function(e) {
                                if (file.type.startsWith('image/')) {
                                    const img = new Image();
                                    img.src = e.target.result;
                                    item.innerHTML = img.outerHTML;
                                } else {
                                    item.innerHTML = '<i class="fas fa-file"></i>';
                                }
                                
                                const remove = document.createElement('button');
                                remove.type = 'button';
                                remove.className = 'file-preview-remove';
                                remove.innerHTML = '<i class="fas fa-times"></i>';
                                remove.onclick = (e) => {
                                    e.preventDefault();
                                    item.remove();
                                };
                                item.appendChild(remove);
                                preview.appendChild(item);
                            };
                            reader.readAsDataURL(file);
                        });
                    }
                });
            }
        });
        JS;

        $this->view->registerJs($js);

        return $html;
    }
}
