<?php

namespace App\Services;

use App\Exceptions\FacebookApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AnuncioService extends FacebookAbstractService
{
    /** Lista anúncios da conta */
    public function getAnuncios(array $query = []): array
    {
        $url = $this->urlForAccount('ads');

        $resp = $this->get($url, [
            'fields' => $this->fields([
                'id',
                'name',
                'status',
                'effective_status',
                'adset_id',
                'creative{id,name,image_url}'
            ]),
        ] + $query);

        $this->throwIfFailed($resp);
        return $this->unwrapData($resp);
    }

    /** Busca um anúncio por ID */
    public function getAnuncioById(string $adId): array
    {
        $adId = $this->ensureId($adId, 'adId');
        $url  = $this->urlForId($adId);

        $resp = $this->get($url, [
            'fields' => $this->fields([
                'id',
                'name',
                'status',
                'effective_status',
                'adset_id',
                'creative{id,name,image_url}'
            ]),
        ]);

        $this->throwIfFailed($resp);
        return $resp->json() ?? [];
    }

    /** Cria o criativo de imagem (antes de criar o Ad) */
    private function uploadCreative(string $filePath, string $name): ?string
    {
        $url = $this->urlForAccount('adcreatives');
        $resp = Http::asMultipart()->post($url, [
            ['name' => 'access_token', 'contents' => $this->accessToken],
            ['name' => 'name', 'contents' => $name],
            [
                'name'     => 'object_story_spec',
                'contents' => json_encode([
                    'page_id' => config('services.facebook.page_id'),
                    'link_data' => [
                        'message'    => 'Anúncio criado via Construleads',
                        'link'       => config('app.url'),
                        'image_hash' => $this->uploadImage($filePath),
                    ],
                ]),
            ],
        ]);

        if ($resp->failed()) {
            throw FacebookApiException::fromResponse($resp);
        }

        $json = $resp->json();
        return $json['id'] ?? null;
    }

    /** Faz upload de imagem e retorna image_hash */
    private function uploadImage(string $filePath): ?string
    {
        $url = $this->urlForAccount('adimages');
        $resp = Http::attach(
            'bytes',
            file_get_contents($filePath),
            basename($filePath)
        )->post($url, ['access_token' => $this->accessToken]);

        if ($resp->failed()) {
            throw FacebookApiException::fromResponse($resp);
        }

        $json = $resp->json();
        return $json['images'][basename($filePath)]['hash'] ?? null;
    }

    /** Cria um anúncio */
    public function createAnuncio(array $data, ?string $filePath = null): array
    {
        $url = $this->urlForAccount('ads');

        $payload = [
            'name'     => $data['name'] ?? 'Anúncio',
            'adset_id' => $data['adset_id'],
            'status'   => $data['status'] ?? 'PAUSED',
        ];

        // Se houver imagem, cria o creative primeiro
        if ($filePath) {
            $creativeId = $this->uploadCreative($filePath, $payload['name']);
            $payload['creative'] = json_encode(['creative_id' => $creativeId]);
        } elseif (!empty($data['creative_id'])) {
            $payload['creative'] = json_encode(['creative_id' => (string) $data['creative_id']]);
        } else {
            throw new \InvalidArgumentException('É necessário informar um criativo (imagem ou creative_id).');
        }

        $resp = Http::asForm()->post($url, array_merge($payload, [
            'access_token' => $this->accessToken,
        ]));

        $this->throwIfFailed($resp);

        $json = $resp->json();
        $id = $json['id'] ?? null;
        return $id ? $this->getAnuncioById($id) : ($json ?? ['success' => true]);
    }

    /** Atualiza o anúncio */
    public function updateAnuncio(string $adId, array $data): array
    {
        $adId = $this->ensureId($adId, 'adId');
        $url  = $this->urlForId($adId);

        $payload = [];
        foreach (['name', 'status'] as $key) {
            if (!empty($data[$key])) $payload[$key] = $data[$key];
        }

        if (!empty($data['creative_id'])) {
            $payload['creative'] = json_encode(['creative_id' => (string)$data['creative_id']]);
        }

        $resp = Http::asForm()->post($url, array_merge($payload, [
            'access_token' => $this->accessToken,
        ]));

        $this->throwIfFailed($resp);
        return $this->getAnuncioById($adId);
    }

    /** Deleta anúncio */
    public function deleteAnuncio(string $adId): bool
    {
        $adId = $this->ensureId($adId, 'adId');
        $url  = $this->urlForId($adId);

        $resp = Http::delete($url, ['access_token' => $this->accessToken]);
        $this->throwIfFailed($resp);

        $json = $resp->json();
        return isset($json['success']) ? (bool)$json['success'] : false;
    }
}
