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
 * RequireOne verifies if at least one of [[fields]] is not empty.
 * 
 * @author hmku <hmku@yakoo.com.hk>
 */
class RequireOne extends Validator {

    /**
     * @var string[]
     * The list of attributes to check
     */
    public $fields = [];
    public $skipOnEmpty = false; // disable default skipOnEmpty

    /**
     * @var boolean
     * Whether the validator will check if the attribute value is null;
     * If this property is false, the validator will call [[isEmpty]] to check if the attribute value is empty.
     */
    public $strict = false;

    /**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     *
     * - `{attributes}`: the labels of the attributes being validated
     */
    public $message;

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute) {
        $valid = false;
        foreach ($this->fields as $field) {
            $valid = $valid || $this->notEmpty($model->$field);
        }
        if (!$valid) {
            $model->addError($attribute, Yii::$app->i18n->format($this->message, ["attributes" => $this->getAttributeLabels($model)], Yii::$app->language));
        }
    }

    /**
     * @inheritdoc
     */
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
