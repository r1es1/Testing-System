<?php

class FilterMaster {

    public function __construct() {
        //empty one
    }

    static public function filterSearchQuery($input, $language = null)
    {
        if( is_null($language) )
        {
            $language = \Multilanguage\current_language();
        }

        if( $language == 'ru' )
        {
            return rtrim($input, 'оияа');
        } else
        {
            return rtrim($input, 'eso');
        }
    }

    static public function isRegEmpty($input) {
        if (preg_match('#^\s*$#',$input)) {
            return true;
        } else {
            return false;
        }
    }

    static public function isEmail($input) {
        return filter_var($input, FILTER_VALIDATE_EMAIL);
    }

    static public function isNumber($input) {
        if (preg_match('#^[0-9]+$#', $input)) {
            return true;
        } else {
            return false;
        }
    }

    static public function isFloat($input) {
        if (preg_match('#^[0-9]+\.[0-9]+$#',$input)) {
            return true;
        } else {
            return false;
        }
    }

    static public function isBiggerThanZero($input) {
        return $input > 0;
    }

    static public function validateString($input, $minLength = 0, $maxLength = 0, $symbolsRequired = true, $numbersRequired = true, $availableSpecs = '_') {
        $regExpr = '#^';
        if ($symbolsRequired || $numbersRequired || $availableSpecs != '') {
            $regExpr .= '[';
        }
        if ($symbolsRequired) {
            $regExpr .= 'A-z';
        }
        if ($numbersRequired) {
            $regExpr .= '0-9';
        }
        if ($symbolsRequired || $numbersRequired || $availableSpecs != '') {
            $regExpr .= $availableSpecs.']';
        }
        if ($minLength != 0 || $maxLength != 0) {
            if ($minLength > 0) {
                $regExpr .= '{'.$minLength.',';
            }
            if ($maxLength > 0 && $maxLength > $minLength) {
                $regExpr .= $maxLength;
            }
            $regExpr .= '}';
        }
        $regExpr .= '$#';
        if (preg_match($regExpr, $input)) {
            return true;
        } else {
            return false;
        }
    }

    static public function filterAll($inputStr) {
        $str = htmlspecialchars(strip_tags($inputStr));
        return $str;
    }

}