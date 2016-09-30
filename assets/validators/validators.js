/**
 * Yakoo Validators Javascript
 * 
 * @requires jQuery, yii.validation
 * @link http://www.yakoo.com.hk 
 * @copyright (c) 2016, Yakoo Technology Limited
 * @license MIT
 */
(function ($, yii) {
    "use strict";

    /**
     * Array.prototype.find Polyfill
     * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/find
     */
    var array_find = function (list, predicate) {
        if (list == null) {
            throw new TypeError('list cannot be null');
        }
        if (typeof predicate !== 'function') {
            throw new TypeError('predicate must be a function');
        }
        var length = list.length >>> 0;
        var thisArg = arguments[2];
        var value;

        for (var i = 0; i < length; i++) {
            value = list[i];
            if (predicate.call(thisArg, value, i, list)) {
                return value;
            }
        }
        return undefined;
    };
    var findAttributeByName = function (attributes, name) {
        var byName = function (attr) {
            return attr.name === name;
        };
        if (Array.prototype.find) {
            // Array.prototype.find exists, use built-in function
            return attributes.find(byName);
        } else {
            // use array_find polyfill
            array_find(attributes, byName);
        }
    }
    /**
     * Thanks to http://hknothingblog.blogspot.hk/2013/01/javascript-to-validate-hkid-number.html
     * @param string str
     * @returns Boolean
     */
    var IsHKID = function (str, allowBracket) {
        var strValidChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"

        // basic check length
        if (str.length < 8) {
            return false;
        }

        // handling bracket
        if (allowBracket && str.charAt(str.length - 3) === '(' && str.charAt(str.length - 1) === ')') {
            str = str.substring(0, str.length - 3) + str.charAt(str.length - 2);
        }

        // convert to upper case
        str = str.toUpperCase();
        // regular expression to check pattern and split
        var hkidPat = /^([A-Z]{1,2})([0-9]{6})([A0-9])$/;
        var matchArray = str.match(hkidPat);
        // not match, return false
        if (matchArray === null)
            return false;
        // the character part, numeric part and check digit part
        var charPart = matchArray[1];
        var numPart = matchArray[2];
        var checkDigit = matchArray[3];
        // calculate the checksum for character part
        var checkSum = 0;
        if (charPart.length === 2) {
            checkSum += 9 * (10 + strValidChars.indexOf(charPart.charAt(0)));
            checkSum += 8 * (10 + strValidChars.indexOf(charPart.charAt(1)));
        } else {
            checkSum += 9 * 36;
            checkSum += 8 * (10 + strValidChars.indexOf(charPart));
        }

        // calculate the checksum for numeric part
        for (var i = 0, j = 7; i < numPart.length; i++, j--)
            checkSum += j * numPart.charAt(i);
        // verify the check digit
        var remaining = checkSum % 11;
        var verify = (remaining === 0) ? 0 : 11 - remaining;
        return verify == checkDigit || (verify == 10 && checkDigit === 'A');
    }

    var Yakoo = {};
    Yakoo.validation = {
        maxSelect: function (value, messages, options) {
            var valid = true;
            if ($.isArray(value) && (options.max > 0 && value.length > options.max)) {
                valid = false;
            }
            if (!valid) {
                messages.push(options.message);
            }
        },
        requireOne: function ($form, messages, options) {
            var valid = false;
            var data = $form.data('yiiActiveForm');
            $.each(options.fields, function (i, field) {
                var attr = findAttributeByName(data.attributes, field);
                valid = valid || (attr && !yii.validation.isEmpty(attr.value));
            });
            if (!valid) {
                messages.push(options.message);
            }
        },
        hkid: function (value, messages, options) {
            var valid = yii.validation.isEmpty(value) || IsHKID(value, (options.allowBracket || true));
            if (!valid) {
                messages.push(options.message);
            }
        }
    };
    window.Yakoo = $.extend({}, window.Yakoo, Yakoo); //export 
})(jQuery, yii);
