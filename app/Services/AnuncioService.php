<?php

namespace App\Services;

use App\Services\FacebookAbastractService;
use Illuminate\Support\Facades\Http;

class AnuncioService extends FacebookAbastractService
{
    public function getAnuncios()
    {
        $url = "{$this->baseUrl}{$this->accountId}/ads";

        $response = Http::get($url, [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,effective_status,adset_id,campaign_id,creative{title,body,image_url}',
        ]);

        if ($response->failed()) {
            throw new \Exception("Erro ao acessar API do Facebook: " . $response->body());
        }

        return $response->json()['data'] ?? [];
    }
}
