<?php

use OpenpayStores\OpenpayStoresGateway;
use OpenpayStores\Includes\OpenpayStoresGateway_Blocks_Support;

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
add_action('plugins_loaded', 'OpenpayStoresGateway_init_gateway_class');

// Hook para registrar los scripts de admin
add_action('admin_enqueue_scripts', 'OpenpayStoresGateway_admin_enqueue');

// Hook para cargar el soporte de bloques
add_action('woocommerce_blocks_loaded', 'OpenpayStoresGateway_blocks_support');

// Hook para añadir el enlace de "Ajustes" en la página de plugins
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'OpenpayStoresGateway_settings_link');

// Hook para la compatibilidad con tablas de órdenes de WooCommerce
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
    // Ruta interna del plugin donde guardas las plantillas
    $plugin_path = trailingslashit(plugin_dir_path(__FILE__)) . 'templates/woocommerce/';

    // Si existe una plantilla dentro del plugin con ese nombre se utilizara para el correo
    $plugin_template = $plugin_path . $template_name;

    if (file_exists($plugin_template)) {
        return $plugin_template;
    }

    return $template;
}, 999, 3);

//Hook para llamar scripts personalizados
add_action('wp_enqueue_scripts', 'payment_scripts');

/**
 * Inicializa la pasarela de pago.
 */
function OpenpayStoresGateway_init_gateway_class()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    // Este es el único filtro que necesitas para registrar la pasarela
    add_filter('woocommerce_payment_gateways', 'OpenpayStoresGateway_add_gateway');
}

/**
 * Registra la clase de la pasarela en WooCommerce.
 */
function OpenpayStoresGateway_add_gateway($gateways)
{
    $gateways[] = OpenpayStoresGateway::class;
    return $gateways;
}

/**
 * Registra el script de JavaScript para el panel de administración.
 */
function OpenpayStoresGateway_admin_enqueue($hook)
{
    wp_enqueue_script('openpay_stores_admin_form', plugins_url('assets/js/admin.js', __FILE__), array('jquery'), '1.0.2', true);
}

/**
 * Añade el enlace de "Ajustes" a la lista de acciones del plugin.
 */
function OpenpayStoresGateway_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=openpay_stores') . '">' . __('Ajustes', 'openpay_stores') . '</a>';
    array_unshift($links, $settings_link); // unshift lo pone al principio, es más común
    return $links;
}

/**
 * Registra la integración con los bloques de WooCommerce.
 */
function OpenpayStoresGateway_blocks_support()
{
    if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }

    // Ya no se necesita un 'require_once' aquí porque el autoloader de Composer se encarga de ello.

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            // Usamos la clase con su namespace completo
            $payment_method_registry->register(new OpenpayStoresGateway_Blocks_Support());
        }
    );
}

function payment_scripts(){
    // Validamos si es el checkout por bloques
    global $post;
    if($post && has_block( 'woocommerce/checkout', $post ) || is_checkout()) {
        wp_enqueue_style('openpay-store-checkout-style', plugins_url('assets/css/openpay-store-checkout-style.css', __FILE__));    
    }
    
    if (!is_checkout() ) {
        return;
    }
    wp_enqueue_script('openpay_new_checkout', plugins_url('assets/js/openpay_new_checkout.js', __FILE__), array( 'jquery' ), '', true);
}

//Filtro para personalizar plantillas de WooCommerce
add_filter('woocommerce_locate_template', function ($template, $template_name, $template_path) {
    global $wp;

    // Verificar si es un contexto de orden de Openpay
    $is_openpay_context = false;

    // Verificar en la página de thank you
    if (isset($wp->query_vars['order-received'])) {
        $order_id = absint($wp->query_vars['order-received']);
        if ($order_id) {
            $order = wc_get_order($order_id);
            if ($order && $order->get_payment_method() === 'openpay_stores') {
                $is_openpay_context = true;
            }
        }
    }

    // Verificar en emails (cuando se está renderizando un correo)
    if (!$is_openpay_context && did_action('woocommerce_email_header')) {
        // Intentar obtener el objeto de email actual
        $email = WC()->mailer()->get_emails();
        foreach ($email as $email_obj) {
            if (isset($email_obj->object) && is_a($email_obj->object, 'WC_Order')) {
                $order = $email_obj->object;
                if ($order->get_payment_method() === 'openpay_stores') {
                    $is_openpay_context = true;
                    break;
                }
            }
        }
    }

    // Solo aplicar el filtro si es contexto de Openpay
    if (!$is_openpay_context) {
        return $template;
    }

    // Lista de plantillas permitidas
    $allowed_templates = array(
        'emails/customer-on-hold-order.php'
    );

    if (!in_array($template_name, $allowed_templates)) {
        return $template;
    }

    // Ruta interna del plugin donde guardas las plantillas
    $plugin_path = trailingslashit(plugin_dir_path(__FILE__)) . 'templates/woocommerce/';
    $plugin_template = $plugin_path . $template_name;

    if (file_exists($plugin_template)) {
        return $plugin_template;
    }

    return $template;
}, 999, 3);