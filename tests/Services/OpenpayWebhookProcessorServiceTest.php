<?php

use OpenpayStores\Services\OpenpayWebhookProcessorService;

/**
 * Clase de Pruebas UNITARIAS para OpenpayWebhookProcessorService.
 *
 * Esta clase NO USA FACTORÍAS. Prueba la lógica de negocio
 * en los métodos privados usando un mock de WC_Order.
 *
 * @covers \OpenpayStores\Services\OpenpayWebhookProcessorService
 */
class OpenpayWebhookProcessorServiceTest extends \WP_UnitTestCase
{
    /**
     * @var OpenpayWebhookProcessorService
     */
    private $service;

    /**
     * @var \WC_Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mock_logger;

    /**
     * @var \WC_Order|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mock_order;

    /**
     * Configuración inicial para cada prueba.
     */
    public function setUp(): void
    {
        parent::setUp();

        // 1. Mock del Logger
        $this->mock_logger = $this->createMock(\WC_Logger::class);

        // 2. Mock de WC_Order
        // Creamos un mock que podemos reutilizar en todas las pruebas
        $this->mock_order = $this->createMock(\WC_Order::class);

        // 3. Instanciar el Servicio
        $this->service = new OpenpayWebhookProcessorService();

        // 4. Inyección de Dependencia (Logger)
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('logger');
        $property->setAccessible(true);
        $property->setValue($this->service, $this->mock_logger);
    }

    /**
     * Prueba la lógica de 'charge.succeeded'
     * @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_payment_succeeded
     */
    public function test_handle_payment_succeeded()
    {
        // 1. Preparación: Configurar el mock de la orden
        $this->mock_order->method('is_paid')->willReturn(false);

        // Esperamos que se llame a 'add_order_note'
        $this->mock_order->expects($this->once())
            ->method('add_order_note')
            ->with($this->stringContains('Pago en tiendas completado (ID Transacción: txn_abc123)'));

        // Esperamos que se llame a 'payment_complete'
        $this->mock_order->expects($this->once())
            ->method('payment_complete')
            ->with('txn_abc123');

        // 2. Crear el payload del evento
        $event = $this->get_webhook_event('charge.succeeded', 123, 'txn_abc123');

        // 3. Ejecución: Probar el método privado usando Reflection
        $method = $this->get_accessible_method('handle_payment_succeeded');
        $method->invoke($this->service, $this->mock_order, $event);
    }

    /**
     * Prueba que 'charge.succeeded' se ignore si la orden ya está pagada.
     * @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_payment_succeeded
     */
    public function test_handle_payment_succeeded_already_paid()
    {
        // 1. Preparación: Configurar el mock
        $this->mock_order->method('is_paid')->willReturn(true); // La orden YA está pagada

        // Esperamos que NO se llame a payment_complete
        $this->mock_order->expects($this->never())->method('payment_complete');

        // Esperamos que el log registre el mensaje
        $this->mock_logger->expects($this->once())
            ->method('info')
            ->with($this->stringContains('ya estaba pagada'));

        // 2. Evento
        $event = $this->get_webhook_event('charge.succeeded', 123);

        // 3. Ejecución
        $method = $this->get_accessible_method('handle_payment_succeeded');
        $method->invoke($this->service, $this->mock_order, $event);
    }

    /**
     * Prueba la lógica de 'transaction.expired'.
     * @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_payment_expired
     */
    public function test_handle_payment_expired()
    {
        // 1. Preparación
        $this->mock_order->method('has_status')->with('on-hold')->willReturn(true);

        // Esperamos que se llame a 'update_status'
        $this->mock_order->expects($this->once())
            ->method('update_status')
            ->with('cancelled', $this->stringContains('expirado'));

        // 2. Evento
        $event = $this->get_webhook_event('transaction.expired', 123);

        // 3. Ejecución
        $method = $this->get_accessible_method('handle_payment_expired');
        $method->invoke($this->service, $this->mock_order, $event);
    }

    /**
     * Prueba que 'transaction.expired' se ignore si la orden no está 'on-hold'.
     * @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_payment_expired
     */
    public function test_handle_payment_expired_not_on_hold()
    {
        // 1. Preparación
        $this->mock_order->method('has_status')->with('on-hold')->willReturn(false); // No está 'on-hold'

        // Esperamos que NUNCA se llame a 'update_status'
        $this->mock_order->expects($this->never())->method('update_status');

        // 2. Evento
        $event = $this->get_webhook_event('transaction.expired', 123);

        // 3. Ejecución
        $method = $this->get_accessible_method('handle_payment_expired');
        $method->invoke($this->service, $this->mock_order, $event);
    }

    /**
     * Prueba la lógica de 'charge.failed'.
     * @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_payment_failed
     */
    public function test_handle_payment_failed()
    {
        // 1. Preparación
        $this->mock_order->method('has_status')->with('on-hold')->willReturn(true);

        // Esperamos que se llame a 'update_status'
        $this->mock_order->expects($this->once())
            ->method('update_status')
            ->with('failed', $this->stringContains('falló'));

        // 2. Evento
        $event = $this->get_webhook_event('charge.failed', 123);

        // 3. Ejecución
        $method = $this->get_accessible_method('handle_payment_failed');
        $method->invoke($this->service, $this->mock_order, $event);
    }

    /**
     * Prueba la lógica de 'charge.cancelled'.
     * @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_payment_failed
     */
    public function test_handle_payment_cancelled()
    {
        // 1. Preparación
        $this->mock_order->method('has_status')->with('on-hold')->willReturn(true);

        // Esperamos que se llame a 'update_status'
        $this->mock_order->expects($this->once())
            ->method('update_status')
            ->with('failed', $this->stringContains('falló'));

        // 2. Evento
        $event = $this->get_webhook_event('charge.cancelled', 123);

        // 3. Ejecución
        $method = $this->get_accessible_method('handle_payment_failed');
        $method->invoke($this->service, $this->mock_order, $event);
    }

    // Estas pruebas SÍ pueden probar el método público 'handle_webhook'
    // porque prueban los 'return' tempranos (antes de 'wc_get_order')

    /** @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_webhook */
    public function test_handle_webhook_empty_payload()
    {
        $this->mock_logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('ejecutada sin payload'));

        $this->service->handle_webhook(null);
    }

    /** @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_webhook */
    public function test_handle_webhook_payload_not_string()
    {
        $this->mock_logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('o no es un string'));

        $this->service->handle_webhook(['foo' => 'bar']); // Pasar un array
    }

    /** @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_webhook */
    public function test_handle_webhook_invalid_json()
    {
        $this->mock_logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Payload de webhook inválido'));

        $this->service->handle_webhook('{"invalid_json": "missing_brace"');
    }

    /** @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_webhook */
    public function test_handle_webhook_missing_type()
    {
        $this->mock_logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('inválido o no es JSON'));

        $this->service->handle_webhook('{"foo": "bar"}'); // JSON válido, pero sin 'type'
    }

    /** @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_webhook */
    public function test_handle_webhook_missing_order_id()
    {
        $this->mock_logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('recibido sin order_id'));

        $this->service->handle_webhook('{"type": "charge.succeeded", "transaction": {}}');
    }


    // --- Métodos de Ayuda (Helpers) ---

    /**
     * Helper para obtener un método privado o protegido como accesible.
     * @param string $method_name
     * @return \ReflectionMethod
     */
    private function get_accessible_method($method_name)
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod($method_name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Helper para crear un objeto de evento simulado.
     * (Ya no necesitamos el payload de string)
     */
    private function get_webhook_event($type, $order_id, $txn_id = 'txn_12345', $event_id = 'evt_abcde')
    {
        $event = new \stdClass();
        $event->type = $type;
        $event->id = $event_id;

        $event->transaction = new \stdClass();
        $event->transaction->order_id = $order_id;
        $event->transaction->id = $txn_id;

        return $event;
    }


    // tests para cubrir logica de notificaciones

    /**
     * Prueba la línea 61: if (!$order)
     * (Asumiendo que wc_get_order() devuelve false)
     * @covers \OpenpayStores\Services\OpenpayWebhookProcessorService::handle_webhook
     */
    public function test_handle_webhook_order_not_found()
    {
        // 1. Preparación: Creamos un payload que pasará la validación
        $payload = $this->get_webhook_payload_string('charge.succeeded', 999999);

        // 2. Configurar Logger
        // Esperamos que se registre el error "no encontrado"
        $this->mock_logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Webhook para la orden 999999 no encontrado.'));

        // 3. Ejecución
        // wc_get_order(999999) devolverá false, activando la línea 61
        $this->service->handle_webhook($payload);
    }


    // --- Helper para Payload de String ---
    private function get_webhook_payload_string($type, $order_id, $txn_id = 'txn_12345', $event_id = 'evt_abcde')
    {
        $event = [
            'type' => $type,
            'id' => $event_id,
            'transaction' => [
                'order_id' => $order_id,
                'id' => $txn_id,
            ]
        ];
        return json_encode($event);
    }
}