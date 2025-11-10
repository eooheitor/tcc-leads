<?php

namespace App\Services;

use App\Exceptions\FacebookApiException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdSetService extends FacebookAbstractService
{
    /** Lista AdSets da CONTA (pode filtrar por campanha com query) */
    public function getAdSets(array $query = []): array
    {
        $url = $this->urlForAccount('adsets');

        $resp = $this->get($url, [
            'fields' => $this->fields([
                'id',
                'name',
                'status',
                'effective_status',
                'campaign_id',
                'optimization_goal',
                'billing_event',
                'bid_amount',
                'daily_budget',
                'start_time',
                'end_time',
            ]),
            'limit' => 50,
        ] + $query);

        $this->throwIfFailed($resp);
        return $this->unwrapData($resp);
    }

    /** Lista AdSets de uma campanha específica */
    public function getAdSetsByCampaign(string $campaignId): array
    {
        $campaignId = $this->ensureId($campaignId, 'campaignId');
        $url = $this->urlForId($campaignId) . '/adsets';

        $resp = $this->get($url, [
            'fields' => $this->fields([
                'id',
                'name',
                'status',
                'effective_status',
                'campaign_id',
                'optimization_goal',
                'billing_event',
                'bid_amount',
                'daily_budget',
                'start_time',
                'end_time',
            ]),
            'limit' => 50,
        ]);

        $this->throwIfFailed($resp);
        return $this->unwrapData($resp);
    }

    /** Busca 1 AdSet por ID */
    public function getAdSetById(string $adsetId): array
    {
        $adsetId = $this->ensureId($adsetId, 'adsetId');
        $url = $this->urlForId($adsetId);

        $resp = $this->get($url, [
            'fields' => $this->fields([
                'id',
                'name',
                'status',
                'effective_status',
                'campaign_id',
                'optimization_goal',
                'billing_event',
                'bid_amount',
                'daily_budget',
                'start_time',
                'end_time',
            ]),
        ]);

        if ($resp->failed()) {
            Log::error('FB get adset failed', [
                'adset_id'      => $adsetId,
                'url'           => $url,
                'status'        => $resp->status(),
                'response_json' => $resp->json(),
                'response_raw'  => $resp->body(),
            ]);

            throw \App\Exceptions\FacebookApiException::fromResponse($resp);
        }

        $this->throwIfFailed($resp);
        return $resp->json() ?? [];
    }

    /** Cria AdSet */
    public function createAdSet(array $data): array
    {
        $url = $this->urlForAccount('adsets');
        $campaignId = $this->ensureId((string)($data['campaign_id'] ?? ''), 'campaign_id');

        // 2) Buscar campanha para saber objective, buying_type e se é CBO
        $campResp = $this->get($this->urlForId($campaignId), [
            'fields' => $this->fields([
                'id',
                'objective',
                'buying_type',
                'daily_budget',
                'lifetime_budget',
                // 'is_campaign_budget_optimization',
            ]),
        ]);

        $this->throwIfFailed($campResp);
        $camp   = $campResp->json() ?? [];
        $obj    = $camp['objective']    ?? null;
        $isCbo  = !empty($camp['daily_budget']) || !empty($camp['lifetime_budget']);

        // 3) Datas ISO8601 UTC
        $start = !empty($data['start_time'])
            ? Carbon::parse($data['start_time'] . ' 00:00:00', config('app.timezone'))->utc()->toIso8601String()
            : null;

        $end = !empty($data['end_time'] ?? $data['stop_time'] ?? null)
            ? Carbon::parse(($data['end_time'] ?? $data['stop_time']) . ' 23:59:59', config('app.timezone'))->utc()->toIso8601String()
            : null;

        $targeting = $data['targeting'] ?? [
            'geo_locations' => ['countries' => [$data['country'] ?? 'BR']],
            'age_min'       => (int)($data['age_min'] ?? 18),
            'age_max'       => (int)($data['age_max'] ?? 65),
        ];

        $promoted = [];
        if ($obj === 'OUTCOME_LEADS' || $obj === 'LEAD_GENERATION') {
            $pageId = config('services.facebook.page_id');
            if (!$pageId) {
                throw new \RuntimeException('Defina services.facebook.page_id para campanhas de Leads.');
            }
            $promoted['page_id'] = $pageId;
        } elseif ($obj === 'APP_INSTALLS') {
            $appId = config('services.facebook.app_id');
            if (!$appId) {
                throw new \RuntimeException('Defina services.facebook.app_id para campanhas de App Installs.');
            }
            $promoted['application_id'] = $appId;
        } elseif (in_array($obj, ['CONVERSIONS', 'OFFSITE_CONVERSIONS'])) {
            $pixelId = config('services.facebook.pixel_id');
            if ($pixelId) {
                $promoted['pixel_id'] = $pixelId;
            }
        }

        $payload = [
            'name'              => $data['name'] ?? null,
            'campaign_id'       => $campaignId,
            'status'            => $data['status'] ?? 'PAUSED',
            'optimization_goal' => $data['optimization_goal'] ?? null,
            'billing_event'     => $data['billing_event'] ?? null,
            'targeting'         => json_encode($targeting),
        ];

        if (!empty($data['bid_strategy'])) {
            $payload['bid_strategy'] = $data['bid_strategy'];
        }
        if (!empty($data['bid_amount'])) {
            $payload['bid_amount'] = (string)((int)$data['bid_amount']); // centavos
        }

        // Em CBO, NÃO enviar daily_budget
        if (!$isCbo && !empty($data['daily_budget'])) {
            $daily = (int)$data['daily_budget'];
            if ($daily < 200) $daily = 200;
            $payload['daily_budget'] = (string)$daily;
        }

        if ($start) $payload['start_time'] = $start;
        if ($end)   $payload['end_time']   = $end;
        if (!empty($promoted)) {
            $payload['promoted_object'] = json_encode($promoted);
        }

        Log::debug('FB create adset: request', [
            'endpoint' => $url,
            'objective' => $obj,
            'cbo'      => $isCbo,
            'payload'  => array_diff_key($payload, [
                'targeting'        => 1,
                'promoted_object'  => 1,
            ]),
            'has_targeting' => isset($payload['targeting']),
            'has_promoted'  => isset($payload['promoted_object']),
        ]);

        $resp = $this->postForm($url, $payload);

        if ($resp->failed()) {
            Log::error('FB create adset failed', [
                'endpoint'      => $url,
                'status'        => $resp->status(),
                'response_json' => $resp->json(),
                'response_raw'  => $resp->body(),
            ]);
            throw FacebookApiException::fromResponse($resp);
        }

        $json = $resp->json();
        $id   = $json['id'] ?? null;

        return $id ? $this->getAdSetById($id) : ($json ?? ['success' => true]);
    }

    /** Atualiza AdSet */
    public function updateAdSet(string $adsetId, array $data): array
    {
        $adsetId = $this->ensureId($adsetId, 'adsetId');
        $url = $this->urlForId($adsetId);

        $payload = [];

        foreach (['name', 'status', 'optimization_goal', 'billing_event', 'start_time', 'end_time'] as $k) {
            if (isset($data[$k]) && $data[$k] !== '') $payload[$k] = $data[$k];
        }
        foreach (['daily_budget', 'bid_amount'] as $money) {
            if (isset($data[$money]) && $data[$money] !== '') $payload[$money] = (string)$data[$money];
        }
        if (!empty($data['targeting']) && is_array($data['targeting'])) {
            $payload['targeting'] = json_encode($data['targeting']);
        }

        $resp = $this->postForm($url, $payload);
        if ($resp->failed()) {
            throw FacebookApiException::fromResponse($resp);
        }

        return $this->getAdSetById($adsetId);
    }

    /** Deleta AdSet */
    public function deleteAdSet(string $adsetId): bool
    {
        $adsetId = $this->ensureId($adsetId, 'adsetId');
        $url = $this->urlForId($adsetId);

        $resp = $this->delete($url);
        $this->throwIfFailed($resp);

        $json = $resp->json();
        return isset($json['success']) ? (bool)$json['success'] : false;
    }
}
