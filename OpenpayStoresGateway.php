<?php

use OpenpayStores\Includes\OpenpayStoresUtils;

if (!class_exists('Openpay')) {
    require_once("lib/openpay/Openpay.php");
}

if (!class_exists('OpenpayStoresUtils')) {
    require_once("utils/OpenpayStoresUtils.php");
}

/*
  Title:	Openpay Payment extension for WooCommerce
  Author:	Openpay
  URL:		http://www.openpay.mx
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html
    WC requires at least: 3.0
    WC tested up to: 8.0.*
 */

class OpenpayStoresGateway extends WC_Payment_Gateway
{
    const VERSION_NUMBER_ADMIN_SCRIPT = '1.0.0';
    
    protected $GATEWAY_NAME = "Openpay Stores";
    protected $is_sandbox = true;
    protected $order = null;
    protected $transaction_id = null;
    protected $transactionErrorMessage = null;
    protected $currencies;
    protected $logger = null;
    protected $country = '';
    protected $iva = 0;
    protected $test_merchant_id;
    protected $test_private_key;
    protected $live_merchant_id;
    protected $live_private_key;
    protected $deadline;
    protected $merchant_id;
    protected $private_key;
    protected $pdf_url_base;
    protected $images_dir;


    public function __construct() {
        $this->id = 'openpay_stores';
        $this->method_title = __('Pago seguro con efectivo', 'openpay_stores');
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();
        $this->logger = wc_get_logger();

        $this->country = $this->settings['country'];
        $this->currencies = UtilsStores::getCurrencies($this->country);        
        $this->iva = $this->country == 'CO' ? $this->settings['iva'] : 0;
        $this->description = '';
        $this->is_sandbox = strcmp($this->settings['sandbox'], 'yes') == 0;
        $this->test_merchant_id = $this->settings['test_merchant_id'];
        $this->test_private_key = $this->settings['test_private_key'];
        $this->live_merchant_id = $this->settings['live_merchant_id'];
        $this->live_private_key = $this->settings['live_private_key'];        
        $this->deadline = $this->settings['deadline'];
        $this->merchant_id = $this->is_sandbox ? $this->test_merchant_id : $this->live_merchant_id;        
        $this->private_key = $this->is_sandbox ? $this->test_private_key : $this->live_private_key;
        $this->pdf_url_base = OpenpayStoresUtils::getUrlPdfBase($this->is_sandbox, $this->country);

    }

    public function init_form_fields() {                
        $this->form_fields = array(
            'enabled' => array(
                'type' => 'checkbox',
                'title' => __('Habilitar módulo', 'woothemes'),
                'label' => __('Habilitar', 'woothemes'),
                'default' => 'yes'
            ),            
            'sandbox' => array(
                'type' => 'checkbox',
                'title' => __('Modo de pruebas', 'woothemes'),
                'label' => __('Habilitar', 'woothemes'),                
                'default' => 'no'
            ),
            'country' => array(
                'type' => 'select',
                'title' => __('País', 'woothemes'),                             
                'default' => 'MX',
                'options' => array(
                    'MX' => 'México',
                    'CO' => 'Colombia',
                    'PE' => 'Perú'
                )
            ),
            'test_merchant_id' => array(
                'type' => 'text',
                'title' => __('ID de comercio de pruebas', 'woothemes'),
                'description' => __('Obten tus llaves de prueba de tu cuenta de Openpay.', 'woothemes'),
                'default' => __('', 'woothemes')
            ),
            'test_private_key' => array(
                'type' => 'text',
                'title' => __('Llave secreta de pruebas', 'woothemes'),
                'description' => __('Obten tus llaves de prueba de tu cuenta de Openpay ("sk_").', 'woothemes'),
                'default' => __('', 'woothemes')
            ),            
            'live_merchant_id' => array(
                'type' => 'text',
                'title' => __('ID de comercio de producción', 'woothemes'),
                'description' => __('Obten tus llaves de producción de tu cuenta de Openpay.', 'woothemes'),
                'default' => __('', 'woothemes')
            ),
            'live_private_key' => array(
                'type' => 'text',
                'title' => __('Llave secreta de producción', 'woothemes'),
                'description' => __('Obten tus llaves de producción de tu cuenta de Openpay ("sk_").', 'woothemes'),
                'default' => __('', 'woothemes')
            ),            
            'deadline' => array(
                'type' => 'number',
                'required' => true,
                'title' => __('Payment deadline', 'woothemes'),
                'description' => __('Define how many hours have the customer to make the payment.', 'woothemes'),
                'default' => '48'
            ),
            'iva' => array(
                'type' => 'number',
                'required' => true,
                'title' => __('IVA', 'woothemes'),                
                'default' => '0',
                'id' => 'openpay_show_iva',                
            ),
        );
    }

    public function openpay_stores_admin_enqueue() {}
    function payment_scripts(){}
    public function process_admin_options() {}
    public function webhook_handler() {}

    public function admin_options() {}
    public function payment_fields() {}
    protected function processOpenpayCharge() {}

    public function process_payment() {}
    public function createOpenpayCharge() {}
    public function getOpenpayCustomer() {}
    public function createOpenpayCustomer() {}
    private function formatAddress() {}
    public function hasAddress() {}
    public function createWebhook() {}

    public function error() {}
    public function errorWebhook() {}
    public function validateCurrency() {}
    public function isNullOrEmptyString() {}
    public function getOpenpayInstance() {}
}
