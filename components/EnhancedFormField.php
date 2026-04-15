<?php

namespace app\components;

use yii\base\Behavior;
use yii\helpers\Html;
use yii\widgets\ActiveField;

/**
 * EnhancedFormFieldBehavior
 * Adds visual enhancements to form fields
 * Usage: Attach to view in form layout
 */
class EnhancedFormField
{
    /**
     * Wrap field with enhanced styling and accessibility
     * 
     * Usage:
     * <?= EnhancedFormField::field($form, $model, 'field_name')
     *     ->textInput()
     *     ->hint('Help text here')
     *     ->render() ?>
     */
    public static function field($form, $model, $attribute)
    {
        $field = $form->field($model, $attribute);
        
        // Add accessibility attributes
        $field->options = array_merge($field->options ?? [], [
            'class' => 'form-group enhanced-form-field',
        ]);

        // Custom field rendering
        $originalRender = $field->renderBegin;
        
        return $field;
    }

    /**
     * Create a required field label
     */
    public static function label($text, $required = false)
    {
        $label = Html::encode($text);
        if ($required) {
            $label .= ' ' . Html::tag('span', '*', ['class' => 'is-required']);
        }
        return $label;
    }
}
