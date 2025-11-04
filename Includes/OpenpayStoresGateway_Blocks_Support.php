<?php

namespace OpenpayStores\Includes;

use OpenpayStores\OpenpayStoresGateway;
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class OpenpayStoresGateway_Blocks_Support extends AbstractPaymentMethodType {

    protected $name = 'openpay_stores';

    public function initialize() {
        // get payment gateway settings
        $this->settings = get_option( "woocommerce_{$this->name}_settings", array() );

        add_action( 'woocommerce_rest_checkout_process_payment_with_context', function( $context, $result ) {
            $logger = wc_get_logger();
            $logger->debug('CONTEXT->PAYMENT_METHOD - ' . json_encode($context->payment_method));
            if ( $context->payment_method === 'openpay_stores' ) {
                $logger->debug('CONTEXT->FRONT_PAYMENT_DATA - ' . json_encode($context->payment_data));
            }
        }, 10, 2 );
    }

    public function is_active() {
        return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
    }

    public function get_payment_method_script_handles() {
        $assets_path = plugin_dir_path( __DIR__ ) . 'blocks/checkout-form/build/index.asset.php';
        //var_dump($assets_file);
        $version      = null;
        $dependencies = array();

        if( file_exists( $assets_path ) ) {
            $asset        = require $assets_path;
            $version      = isset( $asset[ 'version' ] ) ? $asset[ 'version' ] : $version;
            $dependencies = isset( $asset[ 'dependencies' ] ) ? $asset[ 'dependencies' ] : $dependencies;
        }

        wp_register_script(
            'openpay-stores-blocks-integration',
            plugin_dir_url( __DIR__ ) . '/blocks/checkout-form/build/index.js',
            $dependencies,
            $version,
            true
        );
        return array( 'openpay-stores-blocks-integration' );
    }

    public function get_payment_method_data() {
        $openpay_gateway = new OpenpayStoresGateway();

        return array(
            'country' => $openpay_gateway->country,
            'images_dir' => $openpay_gateway->images_dir
        );
    }

}