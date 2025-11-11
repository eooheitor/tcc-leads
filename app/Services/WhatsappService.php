<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected string $phoneNumberId;
    protected string $token;

    public function __construct()
    {
        $this->phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $this->token         = (string) config('services.whatsapp.token');
    }

    /**
     * Envia uma mensagem de texto simples via WhatsApp Cloud API.
     *
     * @throws \RuntimeException em caso de falha na API
     */
    public function sendText(string $toE164, string $body): array
    {
        if (!$this->phoneNumberId || !$this->token) {
            throw new \RuntimeException('WhatsApp API não configurada (phone_number_id ou token ausente).');
        }

        $url = "https://graph.facebook.com/v20.0/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $toE164, // ex.: 5547999999999
            'type'              => 'text',
            'text'              => [
                'preview_url' => false,
                'body'        => $body,
            ],
        ];

        Log::info('WhatsApp sendText request', [
            'url'     => $url,
            'to'      => $toE164,
            'payload' => $payload,
        ]);

        $resp = Http::withToken($this->token)
            ->acceptJson()
            ->post($url, $payload);

        if ($resp->failed()) {
            $json = $resp->json();
            Log::error('WhatsApp sendText failed', [
                'status'  => $resp->status(),
                'body'    => $resp->body(),
                'json'    => $json,
            ]);

            $msg = data_get($json, 'error.message') ?? 'Falha ao enviar mensagem pelo WhatsApp.';
            throw new \RuntimeException($msg);
        }

        $json = $resp->json() ?? [];
        Log::info('WhatsApp sendText success', ['response' => $json]);

        return $json;
    }
}
