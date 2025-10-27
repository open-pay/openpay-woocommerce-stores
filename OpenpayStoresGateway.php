<?php

namespace OpenpayStores;

use Openpay\Data\OpenpayApi;
use OpenpayStores\Includes\OpenpayClient;
use OpenpayStores\Includes\OpenpayUtils;
use OpenpayStores\Services\PaymentSettings\OpenpayPaymentSettingsValidation;
use WC_Payment_Gateway;
use WC_Admin_Settings;
use OpenpayStores\Services\OpenpayWebhookService;
use OpenpayStores\Services\OpenpayChargeService;
use OpenpayStores\Services\OpenpayCustomerService;

/*
  Title:    Openpay Payment extension for WooCommerce
  Author:   Openpay
  URL:      http://www.openpay.mx
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
    public $country = '';
    protected $iva = 0;
    protected $deadline;
    protected $merchant_id;
    protected $private_key;
    protected $pdf_url_base;
    protected $images_dir;


    public function __construct()
    {
        $this->id = 'openpay_stores';
        $this->title = __('Pago seguro con efectivo', 'openpay_stores');
        $this->method_title = __('Pago seguro con efectivo', 'openpay_stores');
        $this->has_fields = true;

        $this->init_form_fields();
        $this->init_settings();
        $this->logger = wc_get_logger();

        // Método para establecer las propiedades según los ajustes actuales
        $this->setup_properties();
        // Engancha el método para guardar las opciones cuando el admin hace clic en "Guardar cambios"
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // Usa el ID de la pasarela para construir el nombre del hook
        add_action('woocommerce_api_' . $this->id, array($this, 'webhook_handler'));
    }

    private function setup_properties()
    {
        $this->country = $this->get_option('country');
        $this->is_sandbox = 'yes' === $this->get_option('sandbox');
        $this->merchant_id = $this->is_sandbox ? $this->get_option('test_merchant_id') : $this->get_option('live_merchant_id');
        $this->private_key = $this->is_sandbox ? $this->get_option('test_private_key') : $this->get_option('live_private_key');

        // Mueve aquí el resto de las propiedades que dependen de los ajustes
        $this->currencies = OpenpayUtils::getCurrencies($this->country);
        $this->pdf_url_base = OpenpayUtils::getUrlPdfBase($this->is_sandbox, $this->country);
        $this->deadline = $this->get_option('deadline');
        $this->iva = $this->country == 'CO' ? $this->get_option('iva') : 0;
    }
    public function get_merchant_id()
    {
        return $this->merchant_id;
    }

    public function get_country()
    {
        return $this->country;
    }

    public function init_form_fields()
    {
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

    public function process_payment($order_id)
    {
        global $woocommerce;
        $order = wc_get_order($order_id);
        $this->openpay = OpenpayClient::getInstance($this->merchant_id, $this->private_key, $this->country, $this->is_sandbox);

        // Obtener el cliente -> si no existe agregar la información al cargo.
        $customer_service = new OpenpayCustomerService($this->openpay, $this->country, $this->is_sandbox);
        $openpay_customer = $customer_service->retrieveCustomer($order);

        $payment_settings = array(
            'openpay_customer' => $openpay_customer,
            'pdf_url_base' => OpenpayUtils::getUrlPdfBase($this->is_sandbox, $this->country),
            'deadline' => $this->deadline,
            'sandbox' => $this->is_sandbox,
            'merchant_id' => $this->merchant_id,
            'country' => $this->country,
            'iva' => $this->iva
        );

        // Se ejecuta la petición de creación de orden de pago
        $charge_service = new OpenpayChargeService($this->openpay, $order, $customer_service);
        $charge = $charge_service->processOpenpayCharge($payment_settings);

        if ($charge) {
            $order->update_status('on-hold', 'En espera de pago');
            wc_reduce_stock_levels($order);
            $woocommerce->cart->empty_cart();
            $order->add_order_note(sprintf("El pago será procesado por %s con ID de transacción: '%s'", $this->GATEWAY_NAME, $charge->id));

            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url($order)
            );
        } else {
            $order->add_order_note(sprintf("%s - Error en la transacción: '%s'", $this->GATEWAY_NAME, $this->transactionErrorMessage));

            if (function_exists('wc_add_notice')) {
                wc_add_notice(__('Error en la transacción: No se pudo completar tu pago.'), $notice_type = 'error');
            } else {
                $woocommerce->add_error(__('Error en la transacción: No se pudo completar tu pago.'), 'woothemes');
            }
            return false;
        }
    }

    public function validate_fields()
    {
        $this->logger->debug('validate_fields - ' . json_encode($_POST));
        return true;
    }



    public function openpay_stores_admin_enqueue()
    {
    }
    function payment_scripts()
    {
    }
    public function process_admin_options()
    {
        // Obtenemos los datos enviados por el usuario.
        $post_data = $this->get_post_data(); // get_post_data() es un método de WC_Payment_Gateway que ya te da los datos del POST.

        $logger = wc_get_logger();
        $logger->info('DATOS ENVIADOS DESDE GATEWAY: ' . json_encode($post_data));

        // Creamos una instancia de nuestro validador.
        $validator = new OpenpayPaymentSettingsValidation($logger, $this->id);

        // Ejecutamos la validación y capturamos la instancia de Openpay (o null).
        $openpay_instance = $validator->validateOpenpayCredentials($post_data);

        $currency_is_valid = $validator->validateCurrency($this->currencies);

        // Comprobamos si todas las validaciones pasaron.
        if ($openpay_instance && $currency_is_valid) {
            // Si todo está bien, llamamos al método padre para que guarde los nuevos datos.
            parent::process_admin_options();

            // Forzamos la recarga de los ajustes desde la base de datos al objeto actual.
            $this->init_settings();

            // ¡Refrescamos las propiedades del objeto con los nuevos valores!
            $this->setup_properties();

            // Usamos un try-catch por si la API de Openpay falla.
            try {
                // Llamamos al método para verificar o crear el webhook.
                $this->ensureWebhookExists($openpay_instance);

                // Añadir un mensaje de éxito para el webhook (en verde).
                \WC_Admin_Settings::add_message(esc_html__('Webhook de Openpay verificado y configurado correctamente.', 'openpay_stores'));
            } catch (\Exception $e) {
                // Si la creación del webhook falla, mostramos un error, pero los ajustes ya se guardaron.
                \WC_Admin_Settings::add_error('Ajustes guardados, pero hubo un error al configurar el webhook: ' . esc_html($e->getMessage()));
                return false;
            }

            return true;
        }

        // Si alguna validación falló, los errores ya se mostraron y no guardamos nada.
        return false;
    }

    /**
     * Orquesta la creación o verificación del webhook.
     */
    private function ensureWebhookExists(OpenpayApi $openpayApi)
    {
        // Creamos nuestro servicio, inyectando la dependencia.
        $webhookService = new OpenpayWebhookService($openpayApi, $this->logger);

        // Construimos la URL del webhook usando una función de WordPress.
        $target_url_pretty = site_url('/wc-api/Openpay_Stores', 'https');
        $target_url_simple = site_url('/index.php?wc-api=Openpay_Stores', 'https');

        $this->logger->info('Iniciando verificación de webhook. URL objetivo: ' . $target_url_pretty);

        try {
            // Logica principal de webhooks
            $result = $webhookService->reconcileWebhooks($target_url_pretty, $target_url_simple);

            $status = $result['status'];
            $webhook_id = $result['id'];

            // El Gateway maneja la respuesta (Responsabilidad del Gateway, conoce 'update_option' y 'WC_Admin_Settings')
            update_option('woocommerce_openpay_stores_webhook_id', $webhook_id);

            if ($status === 'created') {
                \WC_Admin_Settings::add_message(esc_html__('Webhook de Openpay configurado correctamente.', 'openpay_stores'));
            } else {
                \WC_Admin_Settings::add_message(esc_html__('Webhook de Openpay verificado (ya existía).', 'openpay_stores'));
            }
        } catch (\Exception $e) {
            // Captura errores de getAllWebhooks() o createWebhook()
            $this->logger->error('Error mayor en la gestión de webhooks: ' . $e->getMessage());
            // Lanza la excepción para que process_admin_options la atrape y muestre el error.
            throw $e;
        }
    }
    public function webhook_handler()
    {
        // Responde inmediatamente para que Openpay no espere
        @header('HTTP/1.1 200 OK');

        $payload = file_get_contents('php://input');

        // TODO:
        // Aquí iría la lógica para verificar la firma del webhook de Openpay (MUY IMPORTANTE para producción)
        // if ( ! $this->is_valid_signature($payload, $_SERVER['HTTP_OPENPAY_SIGNATURE']) ) {
        //     $this->logger->warning('Petición de webhook con firma inválida recibida.');
        //     exit; // No procesar si no es válido
        // }

        // Pon el procesamiento en la cola de WooCommerce para que se ejecute en segundo plano
        WC()->queue()->add('openpay_stores_process_webhook', array('payload' => $payload));

        exit; // Termina la ejecución para asegurar una respuesta limpia
    }

    public function admin_options()
    {
        include_once('templates/admin.php');
    }


    public function payment_fields()
    {
        echo '<div class="openpay-logos">';
        echo '<img src="' . esc_url(plugins_url('assets/images/newcheckout/openpay-stores-icons.svg', __FILE__)) . '" alt="" />';
        echo '</div>';
        $this->images_dir = plugin_dir_url(__FILE__) . '/assets/images/';
        $this->fonts_dir = plugin_dir_url(__FILE__) . '/assets/Fonts';
        include_once('templates/payment.php');
    }

    protected function processOpenpayCharge()
    {
    }
    public function createOpenpayCharge()
    {
    }
    public function getOpenpayCustomer()
    {
    }
    public function createOpenpayCustomer()
    {
    }
    private function formatAddress()
    {
    }
    public function hasAddress()
    {
    }
    public function createWebhook()
    {
    }

    public function error()
    {
    }
    public function errorWebhook()
    {
    }
    public function validateCurrency()
    {
        return in_array(get_woocommerce_currency(), $this->currencies);
    }
    public function isNullOrEmptyString()
    {
    }
}
