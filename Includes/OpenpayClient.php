<?php
namespace OpenpayStores\Includes;

/**if (!class_exists('Openpay\Data\Openpay')) {
    require_once(dirname(__FILE__) . '/../lib/openpay/Openpay.php');
}*/

use Openpay\Data\Openpay;
use Openpay\Data\OpenpayApi;

class OpenpayClient {

    /**
     * Configura el entorno global de Openpay y devuelve la instancia de la API.
     *
     * @return OpenpayApi
     */
    public static function getInstance(string $merchant_id, string $private_key, string $country, bool $is_sandbox): OpenpayApi {

        Openpay::setClassificationMerchant('general');
        Openpay::setProductionMode(!$is_sandbox);

        $openpay = Openpay::getInstance($merchant_id, $private_key, $country, self::getClientIp());

        $userAgent = "Openpay-WOOC" . strtoupper($country) . "/v2";
        Openpay::setUserAgent($userAgent);
        
        return $openpay;
    }

    private static function getClientIp() {
        //$logger = wc_get_logger();
        //$logger->info('getClientIp'); 
        // Recogemos la IP de la cabecera de la conexión
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   
        {
          $ipAdress = $_SERVER['HTTP_CLIENT_IP'];
        }
        // Caso en que la IP llega a través de un Proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  
        {
          $ipAdress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // Caso en que la IP lleva a través de la cabecera de conexión remota
        else
        {
          $ipAdress = $_SERVER['REMOTE_ADDR'];
        }
        //$logger->debug('IP IN HEADER: ' . $ipAdress);  
        $ipAdress = trim(explode(",", $ipAdress)[0]);
        return $ipAdress;
      }
}