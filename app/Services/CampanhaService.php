<?php

namespace App\Services;

use App\Services\FacebookAbastractService;
use Illuminate\Support\Facades\Http;

class CampanhaService extends FacebookAbastractService
{
    public function getCampanhas()
    {
        $url = "{$this->baseUrl}{$this->accountId}/campaigns";

        $response = Http::get($url, [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,effective_status,daily_budget,lifetime_budget',
        ]);

        if ($response->failed()) {
            throw new \Exception("Erro ao acessar API do Facebook: " . $response->body());
        }

        return $response->json()['data'] ?? [];
    }
}
