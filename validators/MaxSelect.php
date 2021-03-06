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
 * MaxSelect verifies if the attribute value have less than or equals to [[max]] items.
 * The attribute value must be an array object.
 *
 * @author hmku <hmku@yakoo.com.hk>
 */
class MaxSelect extends Validator {

    public $max = 3;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'You cannot select more than {max} options for {attribute}.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value) {
        if ($value !== null && is_array($value)) {
            if (count($value) > $this->max) {
                return [$this->message, [
                        'max' => $this->max
                ]];
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view) {
        $view->registerAssetBundle(ValidatorsAsset::className());
        $options = [];
        $options['max'] = $this->max;
        $options['message'] = Yii::$app->getI18n()->format($this->message, [
            'attribute' => $model->getAttributeLabel($attribute),
            'max' => $this->max,
                ], Yii::$app->language);


        return 'Yakoo.validation.maxSelect(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

}
