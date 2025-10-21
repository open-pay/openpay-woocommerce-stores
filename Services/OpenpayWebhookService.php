<?php

namespace OpenpayStores\Services;

use Openpay\Data\OpenpayApi;
use Openpay\Resources\OpenpayWebhook;

class OpenpayWebhookService
{
    private $openpay;

    /**
     * El constructor recibe la instancia del SDK de Openpay (Inyección de Dependencias).
     */
    public function __construct(OpenpayApi $openpay)
    {
        $this->openpay = $openpay;
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
}
