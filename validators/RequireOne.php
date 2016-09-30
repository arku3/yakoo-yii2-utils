<?php

/**
 * @link http://www.yakoo.com.hk 
 * @copyright (c) 2016, Yakoo Technology Limited
 * @license MIT
 */

namespace yakoo\validators;

use yakoo\assets\ValidatorsAsset;
use Yii;
use yii\validators\Validator;

/**
 * RequireOne
 * 
 * @author hmku <hmku@yakoo.com.hk>
 */
class RequireOne extends Validator {

    public $message;
    public $fields = [];
    public $skipOnEmpty = false;
    public $strict = false;

    public function init() {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('app', 'Must fill in one of {attributes}.');
        }
    }

    private function notEmpty($value) {
        return $this->strict && $value !== null || !$this->strict && !$this->isEmpty(is_string($value) ? trim($value) : $value);
    }

    private function getAttributeLabels($model) {
        $labels = [];
        foreach ($this->fields as $field) {
            $labels[] = $model->getAttributeLabel($field);
        }
        return implode(", ", $labels);
    }

    public function validateAttributes($model, $attributes = null) {
        return parent::validateAttributes($model, $attributes);
    }

    public function validateAttribute($model, $attribute) {
        $valid = false;
        foreach ($this->fields as $field) {
            $valid = $valid || $this->notEmpty($model->$field);
        }
        if (!$valid) {
            $model->addError($attribute, Yii::$app->i18n->format($this->message, ["attributes" => $this->getAttributeLabels($model)], Yii::$app->language));
        }
    }

    public function clientValidateAttribute($model, $attribute, $view) {
        $view->registerAssetBundle(ValidatorsAsset::className());
        $options = [];
        if ($this->strict) {
            $options['strict'] = 1;
        }
        $options['fields'] = $this->fields;
        $options['message'] = Yii::$app->getI18n()->format($this->message, [
            'attributes' => $this->getAttributeLabels($model),
                ], Yii::$app->language);


        return 'Yakoo.validation.requireOne($form, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

}
