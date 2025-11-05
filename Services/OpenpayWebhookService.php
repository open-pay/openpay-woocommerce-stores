<?php

namespace OpenpayStores\Services;

use Openpay\Data\OpenpayApi;
use Openpay\Resources\OpenpayWebhook;
use WC_Logger;

class OpenpayWebhookService
{
    private $openpay;
    private $logger;

    /**
     * El constructor recibe la instancia del SDK de Openpay (Inyección de Dependencias).
     */
    public function __construct(OpenpayApi $openpay, WC_Logger $logger)
    {
        $this->openpay = $openpay;
        $this->logger = $logger;
    }

    /**
     * Busca un webhook existente por su URL.
     * @return \OpenpayWebhook|null El objeto webhook si se encuentra, o null si no.
     */
    public function findWebhookByUrl(string $url)
    {
        $webhooks = $this->openpay->webhooks->getList([]);
        foreach ($webhooks as $webhook) {
            if ($webhook->url === $url) {
                return $webhook;
            }
        }
        return null;
    }

    /**
     * Obtiene la lista completa de webhooks registrados en Openpay.
     *
     * @return array
     */
    public function getAllWebhooks(): array
    {
        // Pedimos un límite alto (ej. 20) para evitar problemas de paginación.
        return $this->openpay->webhooks->getList(['limit' => 20]);
    }

    /**
     * Crea un nuevo webhook en la API de Openpay.
     * @return OpenpayWebhook
     * @throws \Exception Si la creación falla.
     */
    public function createWebhook(string $url): OpenpayWebhook
    {
        $webhook_data = [
            'url' => $url,
            'event_types' => [
                'verification',
                'charge.succeeded',
                'charge.created',
                'charge.cancelled',
                'transaction.expired'
            ]
        ];

        // El método add() arrojará una excepción si falla.
        return $this->openpay->webhooks->add($webhook_data);
    }

    /**
     * Elimina un webhook por su ID.
     *
     * @param string $id El ID del webhook a eliminar.
     * @return void
     * @throws \Exception Si la eliminación falla.
     */
    public function deleteWebhook(string $id)
    {
        try {
            // Obtenemos el objeto webhook por su ID
            $webhook = $this->openpay->webhooks->get($id);
            if ($webhook) {
                // Y llamamos al método delete() sobre ese objeto
                $webhook->delete();
            }
        } catch (\Exception $e) {
            // Relanzamos la excepción para que el orquestador (la pasarela) la maneje.
            throw new \Exception('Error al intentar eliminar el webhook ' . $id . ': ' . $e->getMessage());
        }
    }

    /**
     * Reconcilia los webhooks: busca el activo, crea si no existe, y borra obsoletos.
     *
     * @param string $target_url_pretty La URL "bonita" objetivo (ej: .../wc-api/Openpay_Stores)
     * @param string $target_url_simple La URL "simple" objetivo (ej: .../index.php?wc-api=Openpay_Stores)
     * @return array ['status' => 'found'|'created', 'id' => string]
     * @throws \Exception Si la API falla en un paso crítico (getList o create)
     */
    public function reconcileWebhooks(string $target_url_pretty, string $target_url_simple): array
    {
        // Derivar paths/queries de los objetivos
        $target_path_pretty = parse_url($target_url_pretty, PHP_URL_PATH);
        $target_path_simple = parse_url($target_url_simple, PHP_URL_PATH);
        $target_query_simple = parse_url($target_url_simple, PHP_URL_QUERY);

        // Obtener todos los webhooks
        $all_webhooks = $this->getAllWebhooks();

        $current_webhook_found = null;
        $old_webhooks_to_delete = [];

        // Clasificar webhooks
        foreach ($all_webhooks as $webhook) {
            $webhook_path = parse_url($webhook->url, PHP_URL_PATH);
            $webhook_query = parse_url($webhook->url, PHP_URL_QUERY);

            // --- INICIO DE LOGS DE DEPURACIÓN (DESDE EL SERVICIO) ---
            $this->logger->info('--- COMPARANDO WEBHOOK ---');
            $this->logger->info('Webhook URL: ' . $webhook->url);
            $this->logger->info('Webhook Path (extraído): ' . $webhook_path);
            $this->logger->info('Target Path (objetivo): ' . $target_path_pretty);
            // --- FIN DE LOGS DE DEPURACIÓN ---

            if ($webhook->url === $target_url_pretty) {
                $this->logger->info('Resultado: Encontrado webhook activo (Pretty URL).');
                $current_webhook_found = $webhook;
            } else {
                // Compara si es un webhook "url pretty" de este plugin
                $is_our_pretty_webhook = (strcasecmp(trim($webhook_path), trim($target_path_pretty)) === 0);

                // Compara si es un webhook "url simple" de este plugin
                $is_our_simple_webhook = (strcasecmp(trim($webhook_path), trim($target_path_simple)) === 0) && (strcasecmp(trim($webhook_query), trim($target_query_simple)) === 0);

                if ($is_our_pretty_webhook || $is_our_simple_webhook) {
                    $this->logger->info('Resultado: Encontrado webhook obsoleto. Marcando para borrar.');
                    $old_webhooks_to_delete[] = $webhook;
                } else {
                    $this->logger->info('Resultado: Webhook ignorado (no coincide).');
                }
            }
        }

        // Actuar: Eliminar
        foreach ($old_webhooks_to_delete as $webhook_to_delete) {
            try {
                $this->deleteWebhook($webhook_to_delete->id); //
                $this->logger->info('Webhook obsoleto eliminado: ' . $webhook_to_delete->id);
            } catch (\Exception $e) {
                // No es un error crítico, solo lo logueamos.
                $this->logger->error('Error al intentar eliminar el webhook obsoleto ' . $webhook_to_delete->id . ': ' . $e->getMessage());
            }
        }

        // Actuar: Crear o retornar
        if (is_null($current_webhook_found)) {
            $this->logger->info('El webhook actual no existe. Creando uno nuevo...');
            $newWebhook = $this->createWebhook($target_url_pretty); //
            $this->logger->info('Nuevo webhook creado exitosamente: ' . $newWebhook->id);
            return ['status' => 'created', 'id' => $newWebhook->id];
        } else {
            $this->logger->info('El webhook actual ya estaba registrado. Verificación completa.');
            return ['status' => 'found', 'id' => $current_webhook_found->id];
        }
    }

}
