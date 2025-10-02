<?php
/**
 * PHPUnit bootstrap file.
 */

$_tests_dir = '/tmp/wordpress-tests-lib';

// Carga las funciones de prueba de WordPress.
require_once $_tests_dir . '/tests/phpunit/includes/functions.php';

/**
 * PASO 1: Cargar WooCommerce.
 * Esto es necesario para que clases como WC_Payment_Gateway existan.
 */
function _manually_load_woocommerce() {
    require '/tmp/wordpress/wp-content/plugins/woocommerce/woocommerce.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_woocommerce' );

/**
 * PASO 2: Cargar tu plugin.
 * Ahora sí encontrará la clase de la que hereda.
 */
function _manually_load_plugin() {
    // Asegúrate de que este es el nombre correcto de tu archivo principal.
    require dirname( __DIR__ ) . '/OpenpayStoresGateway.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Inicia el entorno de pruebas de WordPress.
require $_tests_dir . '/tests/phpunit/includes/bootstrap.php';