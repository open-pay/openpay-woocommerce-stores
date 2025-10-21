<?php
namespace OpenpayStores\Tests\Services;

// Importamos las clases que vamos a usar y a probar
use OpenpayStores\Services\PaymentSettings\OpenpayPaymentSettingsValidation;
use WP_UnitTestCase;
use WC_Logger;
use Mockery;

class OpenpayPaymentSettingsValidationTest extends WP_UnitTestCase
{

    private $mock_logger;
    private $gateway_id = 'openpay_stores';

    public function setUp(): void
    {
        parent::setUp();
        // Creamos un "doble" del logger de WooCommerce para cada prueba.
        $this->mock_logger = $this->createMock(WC_Logger::class);
    }

    /**
     * IMPORTANTE: Limpia Mockery después de cada prueba para evitar conflictos.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_validate_credentials_returns_api_instance_on_success()
    {
        // ARRANGE (Preparar)

        // 1. Creamos un mock para el objeto anidado 'webhooks'.
        // Le enseñamos a responder al método 'getList'.
        $mock_webhooks = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['getList'])
            ->getMock();
        $mock_webhooks->method('getList')->willReturn(['un_webhook']);

        // 2. Creamos el mock principal para OpenpayApi (del tipo correcto).
        $mock_openpay_api = $this->createMock(\Openpay\Data\OpenpayApi::class);

        // 3. CLAVE: Configuramos el método mágico __get.
        // "Cuando alguien intente obtener la propiedad 'webhooks'..."
        $mock_openpay_api->method('__get')
            ->with($this->equalTo('webhooks'))
            ->willReturn($mock_webhooks); // "...devuelve nuestro mock de webhooks."

        // 4. Creamos el "mock parcial" de nuestra clase de validación.
        $validator = $this->getMockBuilder(OpenpayPaymentSettingsValidation::class)
            ->setConstructorArgs([$this->mock_logger, $this->gateway_id])
            ->onlyMethods(['createOpenpayApiInstance'])
            ->getMock();

        // 5. Le decimos que devuelva el mock de OpenpayApi que acabamos de configurar.
        $validator->method('createOpenpayApiInstance')
            ->willReturn($mock_openpay_api);

        // 6. Preparamos los datos de configuración simulados.
        $settings_validos = [
            'woocommerce_openpay_stores_sandbox' => '1',
            'woocommerce_openpay_stores_test_merchant_id' => 'id_valido',
            'woocommerce_openpay_stores_test_private_key' => 'sk_valido'
        ];

        // ACT (Actuar)
        // Cuando se ejecute $openpay->webhooks, se activará nuestro mock de __get.
        $result = $validator->validateOpenpayCredentials($settings_validos);

        // ASSERT (Verificar)
        // La aserción ahora pasará porque el objeto devuelto sí es del tipo OpenpayApi.
        $this->assertInstanceOf(\Openpay\Data\OpenpayApi::class, $result);
    }

    /**
     * Prueba el caso de fallo: las credenciales están vacías.
     * (Esta prueba no cambia, ya que no interactúa con el método estático).
     */
    public function test_validate_credentials_returns_null_when_credentials_are_empty()
    {
        // ARRANGE
        $validator = new OpenpayPaymentSettingsValidation($this->mock_logger, $this->gateway_id);
        $settings_invalidos = [
            'woocommerce_openpay_stores_sandbox' => '1',
            'woocommerce_openpay_stores_test_merchant_id' => '',
            'woocommerce_openpay_stores_test_private_key' => ''
        ];

        // ACT
        $result = $validator->validateOpenpayCredentials($settings_invalidos);

        // ASSERT
        $this->assertNull($result);
    }
}