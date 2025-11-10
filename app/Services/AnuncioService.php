<?php

namespace App\Services;

use App\Exceptions\FacebookApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

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
                'campaign_id',
                'creative{id,name}',
            ]),
        ] + $query);

        if ($resp->failed()) {
            Log::error('FB getAnuncios failed', [
                'url'           => $url,
                'query'         => $query,
                'status'        => $resp->status(),
                'response_json' => $resp->json(),
                'response_raw'  => $resp->body(),
            ]);
            throw FacebookApiException::fromResponse($resp);
        }

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
                'campaign_id',
                'creative{
                    id,
                    name,
                    image_url,
                    effective_object_story_id,
                    object_story_spec{
                        link_data{
                            link,
                            message,
                            name,
                            description,
                            call_to_action{type}
                        }
                    },
                    asset_feed_spec
                }'
            ]),
        ]);

        $this->throwIfFailed($resp);
        return $resp->json() ?? [];
    }

    public function getAnunciosByAdset(string $adsetId, array $query = []): array
    {
        $adsetId = $this->ensureId($adsetId, 'adsetId');

        // endpoint: /{adset_id}/ads
        $url  = $this->urlForId($adsetId) . '/ads';

        $resp = $this->get($url, [
            'fields' => $this->fields([
                'id',
                'name',
                'status',
                'effective_status',
                'adset_id',
                'campaign_id',
                'creative{
                    id,
                    name,
                    image_url,
                    effective_object_story_id,
                    object_story_spec{
                        link_data{
                            link,
                            message,
                            name,
                            description,
                            call_to_action{type}
                        }
                    },
                    asset_feed_spec
                }'
            ]),
        ] + $query);
        $this->throwIfFailed($resp);
        return $this->unwrapData($resp);
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
    public function createAnuncio(array $data): array
    {
        $url = $this->urlForAccount('ads');

        $payload = [
            'name'     => $data['name']     ?? null,
            'adset_id' => $data['adset_id'] ?? null,
            'status'   => $data['status']   ?? 'PAUSED',
        ];

        if (!empty($data['creative_id'])) {
            $payload['creative'] = json_encode([
                'creative_id' => (string) $data['creative_id'],
            ]);
        } else {
            // aqui você pode deixar um erro mais claro, se quiser
            throw new \RuntimeException('É necessário informar um creative_id para criar o anúncio.');
        }

        if (!empty($data['tracking_specs']) && is_array($data['tracking_specs'])) {
            $payload['tracking_specs'] = json_encode(array_values($data['tracking_specs']));
        }

        $resp = $this->postForm($url, $payload);
        $this->throwIfFailed($resp);

        $json = $resp->json();
        $id   = $json['id'] ?? null;

        return $id ? $this->getAnuncioById($id) : ($json ?? ['success' => true]);
    }


    public function createFromForm(array $data, ?UploadedFile $image = null): array
    {
        $adsetId = $this->ensureId($data['adset_id'] ?? '', 'adset_id');

        $adName = $data['ad_name'] ?? null;

        // 1) Se já vier com creative_id (cenário futuro), usa direto
        if (!empty($data['creative_id'])) {
            return $this->createAnuncio([
                'name'        => $adName,
                'status'      => $data['status'] ?? 'PAUSED',
                'adset_id'    => $adsetId,
                'creative_id' => $data['creative_id'],
            ]);
        }

        // 2) Upload de imagem é obrigatório neste fluxo
        if (!$image) {
            throw new \RuntimeException('É necessário informar um criativo (imagem ou creative_id).');
        }

        // 2.1) Envia imagem pra conta de anúncios -> retorna image_hash
        $imageHash = $this->uploadAdImage($image);

        // 2.2) Cria o creative de link (imagem única)
        $creativeId = $this->createLinkCreative(
            $data,
            $imageHash
        );

        // 3) Cria o anúncio em si
        return $this->createAnuncio([
            'name'        => $adName,
            'status'      => $data['status'] ?? 'PAUSED',
            'adset_id'    => $adsetId,
            'creative_id' => $creativeId,
        ]);
    }

    /**
     * Sobe a imagem para /{ad_account}/adimages e devolve image_hash.
     */
    protected function uploadAdImage(UploadedFile $file): string
    {
        $url = $this->urlForAccount('adimages');

        // Facebook aceita multipart "source"
        $resp = $this->postMultipart($url, [
            'source' => $file,
        ]);

        $this->throwIfFailed($resp);

        $json   = $resp->json() ?? [];
        $images = $json['images'] ?? [];

        // a chave é o nome do arquivo; pegamos o primeiro hash
        $first  = reset($images) ?: null;
        $hash   = $first['hash'] ?? null;

        if (!$hash) {
            Log::error('uploadAdImage: resposta sem hash', ['json' => $json]);
            throw new \RuntimeException('Não foi possível obter o hash da imagem na Meta.');
        }

        return $hash;
    }

    /**
     * Cria um creative de link (imagem única) usando object_story_spec.
     */
    protected function createLinkCreative(array $data, string $imageHash): string
    {
        $url = $this->urlForAccount('adcreatives');

        $pageId = config('services.facebook.page_id');
        if (!$pageId) {
            throw new \RuntimeException('Defina services.facebook.page_id para criação de criativos.');
        }

        $link       = $data['link_url'] ?? null;
        $message    = $data['primary_text'] ?? null;
        $headline   = $data['headline'] ?? null;
        $desc       = $data['description'] ?? null;
        $ctaType    = $data['call_to_action'] ?: 'LEARN_MORE';

        $objectStorySpec = [
            'page_id'   => (string)$pageId,
            'link_data' => [
                'image_hash' => $imageHash,
                'link'       => $link,
                'message'    => $message,
                'name'       => $headline,
                'description' => $desc,
                'call_to_action' => [
                    'type'  => $ctaType,
                    'value' => [
                        'link' => $link,
                    ],
                ],
            ],
        ];

        $payload = [
            'name'              => $data['name'] ?? 'Creative ' . now()->format('Y-m-d H:i'),
            'object_story_spec' => json_encode($objectStorySpec),
        ];

        $resp = $this->postForm($url, $payload);
        $this->throwIfFailed($resp);

        $json = $resp->json() ?? [];
        $id   = $json['id'] ?? null;

        if (!$id) {
            Log::error('createLinkCreative: resposta sem id', ['json' => $json]);
            throw new \RuntimeException('Não foi possível criar o criativo na Meta.');
        }

        return (string)$id;
    }

    /**
     * Pequeno helper para multipart (se ainda não existir no AbstractService).
     * Ajuste se o seu FacebookAbstractService já tiver algo parecido.
     */
    protected function postMultipart(string $url, array $files = [], array $fields = [])
    {
        $client = \Illuminate\Support\Facades\Http::asMultipart()
            ->withToken($this->accessToken); // ou o jeito que seu AbstractService usa

        foreach ($files as $name => $file) {
            if ($file instanceof UploadedFile) {
                $client = $client->attach(
                    $name,
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                );
            }
        }

        return $client->post($url, $fields);
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

    public function updateFromForm(string $adId, array $data, ?UploadedFile $image = null): array
    {
        $adId = $this->ensureId($adId, 'adId');

        $payload = [];

        if (!empty($data['ad_name'])) {
            $payload['name'] = $data['ad_name'];
        }

        if (!empty($data['status'])) {
            $payload['status'] = $data['status'];
        }

        if (!empty($data['adset_id'])) {
            $payload['adset_id'] = $data['adset_id'];
        }

        // Se veio URL / textos, montamos um novo creative;
        // caso contrário, só trocamos se tiver imagem nova mesmo.
        $hasCreativeFields = !empty($data['link_url'])
            || !empty($data['primary_text'])
            || !empty($data['headline'])
            || !empty($data['description'])
            || !empty($data['call_to_action']);

        if ($image) {
            throw new \RuntimeException('É necessário informar um criativo (imagem ou creative_id).');


            // 2.1) Envia imagem pra conta de anúncios -> retorna image_hash
            $imageHash = $this->uploadAdImage($image);

            // 2.2) Cria o creative de link (imagem única)
            $creativeId = $this->createLinkCreative(
                $data,
                $imageHash
            );
        }

        // remove nulos
        $payload = array_filter($payload, fn($v) => !is_null($v) && $v !== '');

        if (empty($payload)) {
            // nada pra atualizar
            return $this->getAnuncioById($adId);
        }

        $url = $this->urlForId($adId);
        $resp = $this->postForm($url, $payload); // Facebook aceita POST para update

        $this->throwIfFailed($resp);

        // retorna já recarregado
        return $this->getAnuncioById($adId);
    }

    public function deleteAd(string $adId): bool
    {
        $adId = $this->ensureId($adId, 'adId');
        $url  = $this->urlForId($adId);

        $resp = Http::delete($url, [
            'access_token' => $this->accessToken,
        ]);

        if ($resp->failed()) {
            Log::error('FB deleteAd failed', [
                'url'           => $url,
                'status'        => $resp->status(),
                'response_json' => $resp->json(),
                'response_raw'  => $resp->body(),
            ]);
            throw FacebookApiException::fromResponse($resp);
        }

        $json = $resp->json();
        // FB costuma retornar {"success": true}
        return (bool) ($json['success'] ?? true);
    }
}
