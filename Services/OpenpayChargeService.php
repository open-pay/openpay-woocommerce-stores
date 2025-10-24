<?php
namespace OpenpayStores\Services;

// Ensure WordPress functions are available
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class OpenpayChargeService
{

    private $logger;
    private $order;
    private $customer_service;
    private $openpay;

    public function __construct($openpay, $order, $customer_service)
    {
        $this->logger = wc_get_logger();
        $this->order = $order;
        $this->customer_service = $customer_service;
        $this->openpay = $openpay;
    }

    public function processOpenpayCharge($payment_settings)
    {
        $this->logger->info('processOpenpayCharge INIT - ');
        $charge_request = $this->collectChargeData($payment_settings);
        $this->logger->info('processOpenpayCharge charge_request - ' , json_encode($charge_request));
        $charge = $this->create($payment_settings['openpay_customer'], $charge_request);
        $this->logger->info('processOpenpayCharge charge - ' . json_encode($charge));

        if ($charge != false) {
            $this->transaction_id = $charge->id;
            $pdf_url = $payment_settings->pdf_url_base.'/'.$payment_settings->merchant_id. "/".'transaction'."/".$charge->id;
            //WC()->session->set('pdf_url', $pdf_url);
            //Save data for the ORDER
            if ($payment_settings->sandbox) {
                $customer_id = get_user_meta(get_current_user_id(), '_openpay_customer_sandbox_id', true);
            } else {
                $customer_id = get_user_meta(get_current_user_id(), '_openpay_customer_id', true);
            }
            $this->order->update_meta_data('_transaction_id', $charge->id);
            $this->order->update_meta_data('_country', $payment_settings['country']);
            $this->order->update_meta_data('_pdf_url', $pdf_url);

            $this->logger->info('processOpenpayCharge FINISH - ');
            return $charge;
        } else {
            return false;
        }
    }

    public function create($openpay_customer, $charge_request) {
        try {
            if (is_user_logged_in()) {
                return $openpay_customer->charges->create($charge_request);
            }else{
                return $this->openpay->charges->create($charge_request);
            }
        } catch (Exception $e) {
             Throw new Exception ($e);
        }
    }

    private function collectChargeData($payment_settings)
    {
        date_default_timezone_set('America/Mexico_City');
        $this->logger->info('collectChargeData DATE - ' . date('d/m/Y == H:i:s'));
        $due_date = date('Y-m-d\TH:i:s', strtotime('+ '.$payment_settings['deadline'].' hours'));

        $charge_request = array(
            "method" => "store",
            "amount" => number_format((float) $this->order->get_total(), 2, '.', ''),
            "currency" => strtolower(get_woocommerce_currency()),
            "description" => sprintf("Cargo para %s", $this->order->get_billing_email()),
            "order_id" => $this->order->get_id(),
            'due_date' => $due_date,
            "origin_channel" => "PLUGIN_WOOCOMMERCE"
        );

        if (!is_user_logged_in()) {
            $charge_request["customer"] = $this->customer_service->collectCustomerData($this->order);
        }

        if ($payment_settings->country === 'CO') {
            $charge_request['iva'] = $payment_settings->iva;
        }

        return $charge_request;
    }

}









