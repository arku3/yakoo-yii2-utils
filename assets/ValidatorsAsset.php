<?php

/**
 * @link http://www.yakoo.com.hk 
 * @copyright (c) 2016, Yakoo Technology Limited
 * @license MIT
 */

namespace yakoo\assets;

use yii\web\AssetBundle;

/**
 * ValidatorsAsset
 *
 * @author hmku <hmku@yakoo.com.hk>
 */
class ValidatorsAsset extends AssetBundle {

    public $sourcePath = __DIR__ . '/validators';
    public $js = [
        'validators.js',
    ];
    public $depends = [
        'yii\validators\ValidationAsset',
    ];

}
