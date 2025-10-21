<?php

use OpenpayStores\OpenpayStoresGateway;
use OpenpayStores\Includes\WC_Openpay_Gateway_Blocks_Support;

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
if (!defined('ABSPATH')) {
    exit;
}

// ***** INCLUIR EL AUTOLOADER DE COMPOSER *****
// Esto le da a WordPress acceso a todas tus clases con namespace.
require_once __DIR__ . '/vendor/autoload.php';


// Hook para inicializar la pasarela
add_action('plugins_loaded', 'openpay_stores_init_gateway_class');

// Hook para registrar los scripts de admin
add_action('admin_enqueue_scripts', 'openpay_stores_admin_enqueue');

// Hook para cargar el soporte de bloques
add_action('woocommerce_blocks_loaded', 'openpay_stores_blocks_support');

// Hook para añadir el enlace de "Ajustes" en la página de plugins
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'openpay_stores_settings_link');

// Hook para la compatibilidad con tablas de órdenes de WooCommerce
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});


/**
 * Inicializa la pasarela de pago.
 */
function openpay_stores_init_gateway_class()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    // Este es el único filtro que necesitas para registrar la pasarela
    add_filter('woocommerce_payment_gateways', 'add_openpay_stores_gateway');
}

/**
 * Registra la clase de la pasarela en WooCommerce.
 */
function add_openpay_stores_gateway($gateways)
{
    $gateways[] = OpenpayStoresGateway::class;
    return $gateways;
}

/**
 * Registra el script de JavaScript para el panel de administración.
 */
function openpay_stores_admin_enqueue($hook)
{
    wp_enqueue_script('openpay_stores_admin_form', plugins_url('assets/js/admin.js', __FILE__), array('jquery'), '1.0.2', true);
}

/**
 * Añade el enlace de "Ajustes" a la lista de acciones del plugin.
 */
function openpay_stores_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=openpay_stores') . '">' . __('Ajustes', 'openpay_stores') . '</a>';
    array_unshift($links, $settings_link); // unshift lo pone al principio, es más común
    return $links;
}

/**
 * Registra la integración con los bloques de WooCommerce.
 */
function openpay_stores_blocks_support()
{
    if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }

    // Ya no se necesita un 'require_once' aquí porque el autoloader de Composer se encarga de ello.

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            // Usamos la clase con su namespace completo
            $payment_method_registry->register(new WC_Openpay_Gateway_Blocks_Support());
        }
    );
}
