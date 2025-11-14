<?php
namespace OpenpayStores\Services;

/**
 * Procesa los webhooks de Openpay desde la cola de Action Scheduler de WC.
 * Se registra a sí mismo al hook 'openpay_stores_process_webhook'.
 */
class OpenpayWebhookProcessorService
{

    private $logger;

    public function __construct()
    {
        $this->logger = wc_get_logger();

        // Este log nos dice si la clase se está cargando
        // (es decir, si el `new OpenpayWebhookProcessor()` en el Gateway funciona).
        //$this->logger->info('[WebhookProcessor] Clase cargada y hook "openpay_stores_process_webhook" registrado.');

        // ¡Este es el hook que consume la tarea de la cola!
        // Coincide con el nombre usado en WC()->queue()->add()
        add_action('openpay_stores_process_webhook', array($this, 'handle_webhook'), 10, 1);
    }

    /**
     * El manejador real del webhook. Aquí ocurren las "notificaciones".
     *
     * @param array $args Argumentos pasados desde la cola (contiene el payload).
     */
    public function handle_webhook($args)
    {
        // Este es el log MÁS IMPORTANTE. Si ves este, la cola funciona.
        $this->logger->info('[WebhookProcessor] ¡TAREA RECIBIDA! Procesando args: ' . json_encode($args));

        $payload = $args;

        // Verificamos que el payload no esté vacío y sea un string
        if (empty($payload) || !is_string($payload)) {
            $this->logger->error('[WebhookProcessor] Tarea de cola ejecutada sin payload (o no es un string).');
            return;
        }

        $this->logger->info('[WebhookProcessor] Procesando webhook: ' . $payload);

        $event = json_decode($payload);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($event->type)) {
            $this->logger->error('[WebhookProcessor] Error: Payload de webhook inválido o no es JSON.');
            return;
        }

        // Validación básica del objeto del evento
        if (empty($event->transaction->order_id)) {
            $this->logger->warning('[WebhookProcessor] Webhook recibido sin order_id. Ignorando.');
            return; // No podemos hacer nada sin una ID de orden
        }

        $order = wc_get_order($event->transaction->order_id);

        if (!$order) {
            $this->logger->error('[WebhookProcessor] Webhook para la orden ' . $event->transaction->order_id . ' no encontrado.');
            return;
        }

        // --- Lógica de Notificaciones ---
        try {
            switch ($event->type) {

                case 'charge.succeeded':
                    $this->handle_payment_succeeded($order, $event);
                    break;

                case 'transaction.expired':
                    $this->handle_payment_expired($order, $event);
                    break;

                case 'charge.failed':
                case 'charge.cancelled':
                    $this->handle_payment_failed($order, $event);
                    break;

                default:
                    $this->logger->info('[WebhookProcessor] Evento de webhook no manejado: ' . $event->type);
            }
        } catch (\Exception $e) {
            $this->logger->error('[WebhookProcessor] Error al procesar evento: ' . $e->getMessage() . ' | Evento: ' . $payload);
        }
    }

    /**
     * Notificación: El pago fue exitoso.
     */
    private function handle_payment_succeeded($order, $event)
    {
        $transaction_id = $event->transaction->id;

        if ($order->is_paid()) {
            $this->logger->info('[WebhookProcessor] Orden ' . $order->get_id() . ' ya estaba pagada. Ignorando.');
            return;
        }

        $this->logger->info('[WebhookProcessor] Marcando orden ' . $order->get_id() . ' como pagada.');

        $order->add_order_note(
            sprintf(
                __('Pago en tiendas completado (ID Transacción: %s). Evento: %s', 'openpay_stores'),
                $transaction_id,
                $event->id ?? 'N/A' // $event->id es el ID del evento, no del webhook
            )
        );
        $order->payment_complete($transaction_id);
    }

    /**
     * Notificación: La referencia de pago expiró.
     */
    private function handle_payment_expired($order, $event)
    {
        if ($order->has_status('on-hold')) {
            $this->logger->info('[WebhookProcessor] Marcando orden ' . $order->get_id() . ' como cancelada (expirada).');
            $order->update_status('cancelled', __('La referencia de pago en tienda ha expirado.', 'openpay_stores'));
        }
    }

    /**
     * Notificación: El pago falló o fue cancelado.
     */
    private function handle_payment_failed($order, $event)
    {
        if ($order->has_status('on-hold')) {
            $this->logger->info('[WebhookProcessor] Marcando orden ' . $order->get_id() . ' como fallida.');
            $order->update_status('failed', __('El pago en tienda falló o fue cancelado.', 'openpay_stores'));
        }
    }
}