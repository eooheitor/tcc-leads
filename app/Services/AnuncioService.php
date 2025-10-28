<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AnuncioService extends FacebookAbastractService
{
    public function getAnuncios()
    {
        $url = "{$this->baseUrl}{$this->accountPrefix()}ads";

        $response = Http::get($url, [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,effective_status,adset_id,campaign_id,creative{title,body,image_url}',
        ]);

        if ($response->failed()) {
            Log::warning('FB getAnuncios failed', ['url' => $url, 'body' => $response->body()]);
            throw new \Exception("Erro ao acessar API do Facebook: " . $response->body());
        }

        return $response->json()['data'] ?? [];
    }

    public function getAnuncioById(string $adId): array
    {
        $adId = trim($adId);
        if ($adId === '' || !ctype_digit($adId)) {
            throw new \InvalidArgumentException('adId inválido.');
        }

        $url = "{$this->baseUrl}" . $adId;

        $response = Http::get($url, [
            'access_token' => $this->accessToken,
            'fields' => 'id,name,status,effective_status,adset_id,campaign_id,creative{effective_object_story_id,thumbnail_url}',
        ]);

        if ($response->failed()) {
            Log::warning('FB getAnuncioById failed', [
                'ad_id' => $adId,
                'url'   => $url,
                'body'  => $response->body()
            ]);

            $json = $response->json();
            if (isset($json['error']['code'], $json['error']['error_subcode']) &&
                $json['error']['code'] == 100 && $json['error']['error_subcode'] == 33) {
                return []; // devolva array vazio e o controller retorna 404
            }
            throw new \Exception("Erro ao buscar anúncio: " . $response->body());
        }

        return $response->json() ?? [];
    }

    public function updateAnuncio(string $adId, array $data): array
    {
        $adId = trim($adId);
        if ($adId === '' || !ctype_digit($adId)) {
            throw new \InvalidArgumentException('adId inválido.');
        }

        $url = "{$this->baseUrl}" . $adId;

        $payload = ['access_token' => $this->accessToken];

        if (!empty($data['name']))   $payload['name']   = $data['name'];
        if (!empty($data['status'])) $payload['status'] = $data['status']; // ACTIVE, PAUSED, ARCHIVED, DELETED
        if (!empty($data['creative_id'])) {
            $payload['creative'] = json_encode(['creative_id' => $data['creative_id']]);
        }

        $response = Http::asForm()->post($url, $payload);

        if ($response->failed()) {
            Log::warning('FB updateAnuncio failed', [
                'ad_id'   => $adId,
                'url'     => $url,
                'payload' => $payload,
                'body'    => $response->body()
            ]);
            throw new \Exception("Erro ao atualizar anúncio: " . $response->body());
        }

        return $response->json() ?? [];
    }

    public function deleteAnuncio(string $adId): array
    {
        $adId = trim($adId);
        if ($adId === '' || !ctype_digit($adId)) {
            throw new \InvalidArgumentException('adId inválido.');
        }

        $url = "{$this->baseUrl}" . $adId;

        $response = Http::asForm()->delete($url, [
            'access_token' => $this->accessToken,
        ]);

        if ($response->failed()) {
            Log::warning('FB deleteAnuncio failed', [
                'ad_id' => $adId,
                'url'   => $url,
                'body'  => $response->body()
            ]);
            throw new \Exception("Erro ao excluir anúncio: " . $response->body());
        }

        return $response->json() ?? [];
    }
}
