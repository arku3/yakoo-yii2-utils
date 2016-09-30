<?php

use yakoo\assets\ValidatorsAsset;

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
 *  HKID verifies if the attribute value is a valid HKID Number.
 *
 * @author hmku <hmku@yakoo.com.hk>
 */
class HKID extends Validator {

    private static $VALID_CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

    /**
     * @var boolean whether the last digit is allowed to have bracket
     */
    public $allowBracket = true;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'Invalid HKID number.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value) {
        if ($value !== null && !$this->isHkid($value)) {
            return [$this->message, [
            ]];
        }
        return null;
    }

    /**
     * Thanks to http://hknothingblog.blogspot.hk/2013/01/javascript-to-validate-hkid-number.html
     * Converted to PHP implementation
     * 
     * @param string $hkid string to be verified
     * @return boolean if $hkid is a valid HKID number
     */
    protected function isHkid($hkid) {
        $pid = $hkid;
        // basic check length
        if (strlen($pid) < 8) {
            return false;
        }

        // handling bracket
        if ($this->allowBracket) {
            $l = strlen($pid);
            if ($pid[($l - 3)] == '(' && $pid[($l - 1)] == ')') {
                $pid = substr($pid, 0, $l - 3) . $pid[($l - 2)];
            }
        }

        // convert to upper case
        $pid = strtoupper($pid);
        $matches = [];
        // regular expression to check pattern and split
        if (!preg_match('/^([A-Z]{1,2})([0-9]{6})([A0-9])$/', $pid, $matches)) {
            return false;
        }

        $charPart = $matches[1];
        $numPart = $matches[2];
        $checkDigit = $matches[3];

        // calculate the checksum for character part
        $checkSum = 0;
        if (strlen($charPart) === 2) {
            $checkSum += 9 * (10 + strpos(static::$VALID_CHARS, $charPart[0]));
            $checkSum += 8 * (10 + strpos(static::$VALID_CHARS, $charPart[1]));
        } else {
            $checkSum += 9 * 36;
            $checkSum += 8 * (10 + strpos(static::$VALID_CHARS, $charPart));
        }

        // calculate the checksum for numeric part
        for ($i = 0, $j = 7; $i < strlen($numPart); $i++, $j--) {
            $checkSum += $j * ( ord($numPart[$i]) - 48);
        }

        // verify the check digit
        $remaining = $checkSum % 11;
        $verify = ($remaining === 0) ? 0 : 11 - $remaining;
        return $verify == $checkDigit || ($verify == 10 && $checkDigit == 'A');
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view) {
        $view->registerAssetBundle(ValidatorsAsset::className());
        $options = [];
        $options['allowBracket'] = $this->allowBracket;
        $options['message'] = Yii::$app->getI18n()->format($this->message, [
            'attribute' => $model->getAttributeLabel($attribute),
                ], Yii::$app->language);
        return 'Yakoo.validation.hkid(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

}
