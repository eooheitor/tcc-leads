<?php

namespace App\Services;

use App\Services\FacebookAbastractService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CampanhaService extends FacebookAbastractService
{
    public function getCampanhas(): array
    {
        $url = "{$this->baseUrl}{$this->accountPrefix()}campaigns";

        $response = Http::get($url, [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,effective_status,daily_budget,lifetime_budget',
        ]);

        if ($response->failed()) {
            Log::warning('FB getCampanhas failed', ['url' => $url, 'body' => $response->body()]);
            throw new \Exception("Erro ao acessar API do Facebook: " . $response->body());
        }

        return $response->json()['data'] ?? [];
    }

    public function getCampanhaById(string $campaignId): array
    {
        $campaignId = trim($campaignId);
        if ($campaignId === '') {
            throw new \InvalidArgumentException('campaignId inválido.');
        }

        $url = "{$this->baseUrl}{$campaignId}";

        $response = Http::get($url, [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,effective_status,daily_budget,lifetime_budget,start_time,stop_time,objective',
        ]);

        if ($response->failed()) {
            Log::warning('FB getCampanhaById failed', ['id' => $campaignId, 'url' => $url, 'body' => $response->body()]);
            throw new \Exception("Erro ao buscar campanha: " . $response->body());
        }

        return $response->json() ?? [];
    }

    public function updateCampanha(string $campaignId, array $data): array
    {
        $campaignId = trim($campaignId);
        if ($campaignId === '') {
            throw new \InvalidArgumentException('campaignId inválido.');
        }

        $url = "{$this->baseUrl}{$campaignId}";
        $payload = ['access_token' => $this->accessToken];

        if (isset($data['name']) && $data['name'] !== '') {
            $payload['name'] = $data['name'];
        }

        if (isset($data['status']) && $data['status'] !== '') {
            $payload['status'] = $data['status'];
        }

        if (isset($data['daily_budget']) && $data['daily_budget'] !== '') {
            $payload['daily_budget'] = (string) preg_replace('/\D/', '', (string) $data['daily_budget']);
        }
        if (isset($data['lifetime_budget']) && $data['lifetime_budget'] !== '') {
            $payload['lifetime_budget'] = (string) preg_replace('/\D/', '', (string) $data['lifetime_budget']);
        }

        $resp = Http::asForm()->post($url, $payload);

        if ($resp->failed()) {
            Log::warning('FB updateCampanha failed', [
                'id' => $campaignId, 'url' => $url, 'payload' => $payload, 'body' => $resp->body()
            ]);
            throw new \Exception("Erro ao atualizar campanha: " . $resp->body());
        }

        return $this->getCampanhaById($campaignId);
    }

    public function deleteCampanha(string $campaignId): bool
    {
        $campaignId = trim($campaignId);
        if ($campaignId === '' || !ctype_digit($campaignId)) {
            throw new \InvalidArgumentException('campaignId inválido.');
        }

        $url = "{$this->baseUrl}" . $campaignId;

        $response = Http::delete($url, [
            'access_token' => $this->accessToken,
        ]);

        if ($response->failed()) {
            throw new \Exception("Erro ao deletar campanha: " . $response->body());
        }

        $json = $response->json();
        return isset($json['success']) ? (bool)$json['success'] : false;
    }
}
