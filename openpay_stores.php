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

function OpenpayStoresGateway_init_your_gateway() {
    if (class_exists('WC_Payment_Gateway')) {
        include_once('OpenpayStoresGateway.php');
    }
}

function OpenpayStoresGateway_add_gateway($methods) {
    array_push($methods, 'OpenpayStoresGateway');
    return $methods;
}

function OpenpayStoresGateway_settings_link ( $links ) {
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=openpay_stores' ). '">' . __('Ajustes', 'openpay_stores') . '</a>';
    array_push( $links, $settings_link );
    return $links;
}

function openpay_blocks_support() {
    require_once __DIR__ . '/Includes/class-wc-openpay-gateway-blocks-support.php';

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            $payment_method_registry->register( new OpenpayStoresGateway_Blocks_Support );
        }
    );
}

/*
 * This action registers WC_Openpay_Gateway_Blocks_Support class as a WC Payment Block
 */
add_action( 'woocommerce_blocks_loaded', 'openpay_blocks_support' );

add_filter('woocommerce_payment_gateways', 'OpenpayStoresGateway_add_gateway');

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'OpenpayStoresGateway_settings_link' );

add_action('plugins_loaded', 'OpenpayStoresGateway_init_your_gateway', 0);

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );