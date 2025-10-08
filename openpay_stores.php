<?php

 /**
 * Plugin Name: Openpay Stores Plugin
 * Plugin URI: http://www.openpay.mx/docs/plugins/woocommerce.html
 * Description: Provides a cash payment method with Openpay for WooCommerce.
 * Version: 2.0.0
 * Author: Openpay
 * Author URI: http://www.openpay.mx
 * Developer: Openpay
 * Text Domain: openpay-stores
 *
 * WC requires at least: 3.0
 * WC tested up to: 8.5.2
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * 
 * Openpay Docs: http://www.openpay.mx/docs/
 */

 // Evita el acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ***** PASO CLAVE: INCLUIR EL AUTOLOADER DE COMPOSER *****
// Esto le da a WordPress acceso a todas tus clases con namespace.
require_once __DIR__ . '/vendor/autoload.php';

// Importa tu clase principal para usar un nombre corto.
use OpenpayStores\OpenpayStoresGateway;

/**
 * Engánchate a 'plugins_loaded' para inicializar la pasarela de forma segura.
 */
add_action('plugins_loaded', 'openpay_stores_init_gateway_class');
function openpay_stores_init_gateway_class() {
    // Verifica si WooCommerce está activo antes de continuar.
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    // Añade tu pasarela a la lista de pasarelas de WooCommerce.
    add_filter('woocommerce_payment_gateways', 'add_openpay_stores_gateway');
}

/**
 * Registra la clase de la pasarela en WooCommerce.
 *
 * @param array $gateways Lista de pasarelas de pago existentes.
 * @return array Lista de pasarelas de pago con la nuestra añadida.
 */
function add_openpay_stores_gateway($gateways) {
    // Aquí es donde se usa la clase. El autoloader la cargará.
    $gateways[] = OpenpayStoresGateway::class;
    return $gateways;
}