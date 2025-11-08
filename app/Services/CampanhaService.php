<?php

namespace App\Services;
class CampanhaService extends FacebookAbstractService
{
    /** Lista campanhas da conta */
    public function getCampanhas(): array
    {
        $url = $this->urlForAccount('campaigns');

        $resp = $this->get($url, [
            'fields' => $this->fields([
                'id',
                'name',
                'objective',
                'buying_type',
                'daily_budget',
                'special_ad_categories',
                // opcionais
                'status',
                'effective_status',
            ]),
        ]);

        $this->throwIfFailed($resp);
        return $this->unwrapData($resp);
    }

    /** Cria campanha */
    public function createCampanha(array $data): array
    {
        $url = $this->urlForAccount('campaigns');

        $payload = [
            'name'      => $data['name']      ?? null,
            'objective' => $data['objective'] ?? null,
            'status'    => $data['status']    ?? 'PAUSED',
        ];

        if (!empty($data['special_ad_categories'])) {
            $payload['special_ad_categories'] = json_encode(array_values($data['special_ad_categories']));
        }
        if (!empty($data['special_ad_category_country'])) {
            $payload['special_ad_category_country'] = $data['special_ad_category_country'];
        }

        if (!empty($data['daily_budget'])) {
            $payload['daily_budget'] = (string) $data['daily_budget']; // já vem em centavos
        }
        if (!empty($data['lifetime_budget'])) {
            $payload['lifetime_budget'] = (string) $data['lifetime_budget'];
        }

        if (!empty($data['start_time'])) {
            $payload['start_time'] = $data['start_time'];
        }
        if (!empty($data['stop_time'])) {
            $payload['stop_time'] = $data['stop_time'];
        }

        $resp = $this->postForm($url, $payload);
        $this->throwIfFailed($resp);

        $json = $resp->json();
        $id   = $json['id'] ?? null;

        return $id ? $this->getCampanhaById($id) : ($json ?? ['success' => true]);
    }

    /** Busca campanha por ID */
    public function getCampanhaById(string $campaignId): array
    {
        $campaignId = $this->ensureId($campaignId, 'campaignId');
        $url = $this->urlForId($campaignId);

        $resp = $this->get($url, [
            'fields' => $this->fields([
                'id',
                'name',
                'objective',
                'buying_type',
                'daily_budget',
                'special_ad_categories',
                // opcionais
                'status',
                'effective_status',
            ]),
        ]);

        $this->throwIfFailed($resp);
        return $resp->json() ?? [];
    }

    /** Atualiza campanha */
    public function updateCampanha(string $campaignId, array $data): array
    {
        $campaignId = $this->ensureId($campaignId, 'campaignId');
        $url = $this->urlForId($campaignId);

        $payload = [];

        if (isset($data['name']) && $data['name'] !== '') {
            $payload['name'] = $data['name'];
        }
        if (isset($data['status']) && $data['status'] !== '') {
            $payload['status'] = $data['status'];
        }

        if (isset($data['daily_budget']) && $data['daily_budget'] !== '') {
            // aceita int/str de centavos; se vier com formatação, normaliza
            $payload['daily_budget'] = $this->cleanIntString($data['daily_budget']);
        }
        if (isset($data['lifetime_budget']) && $data['lifetime_budget'] !== '') {
            $payload['lifetime_budget'] = $this->cleanIntString($data['lifetime_budget']);
        }

        $resp = $this->postForm($url, $payload);
        $this->throwIfFailed($resp);

        return $this->getCampanhaById($campaignId);
    }

    /** Deleta campanha */
    public function deleteCampanha(string $campaignId): bool
    {
        $campaignId = $this->ensureId($campaignId, 'campaignId');
        $url = $this->urlForId($campaignId);

        $resp = $this->delete($url);
        $this->throwIfFailed($resp);

        $json = $resp->json();
        return isset($json['success']) ? (bool) $json['success'] : false;
    }
}
