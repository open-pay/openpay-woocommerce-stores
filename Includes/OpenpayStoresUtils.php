<?php
namespace OpenpayStores\Includes;
class OpenpayStoresUtils{

    public static function isNullOrEmptyString($string) {
        return (!isset($string) || trim($string) === '');
    }

    public static function getCurrencies($countryCode) {
        switch ($countryCode) {
            case 'MX':
                return ['MXN'];
            case 'CO':
                return ['COP'];
            case 'PE':
                return ['PEN'];
            default:
                break;
        }
    }

    public static function getUrlPdfBase($isSandbox, $countryCode){
        $countryCode = strtolower($countryCode);
        $sandbox = 'https://sandbox-dashboard.openpay.'.$countryCode.'/paynet-pdf';
        $production = 'https://dashboard.openpay.'.$countryCode.'/paynet-pdf';
        $pdfBase = ($isSandbox) ? $sandbox : $production;
        return $pdfBase;   
    }
}