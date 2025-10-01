<?php

namespace OpenpayStores\Includes;
class OpenpayStoresUtils{

    public static function isNullOrEmptyString($string) {
        return (!isset($string) || trim($string) === '');
    }
}