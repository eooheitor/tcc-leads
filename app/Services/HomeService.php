<?php

namespace App\Services;

class HomeService extends FacebookAbstractService
{
    public function getInsights(array $params = []): array
    {
        $days       = $params['days']        ?? 30;
        $campaignId = $params['campaign_id'] ?? null;
        $adSetId    = $params['adset_id']    ?? null;

        $datePreset = match ($days) {
            7  => 'last_7d',
            15 => 'last_14d', // não tem last_15
            30 => 'last_30d',
            default => 'maximum'
        };

        if ($adSetId) {
            $url = $this->urlForId("{$adSetId}/insights");
        } elseif ($campaignId) {
            $url = $this->urlForId("{$campaignId}/insights");
        } else {
            $url = $this->urlForAccount('insights');
        }

        $resp = $this->get($url, [
            'fields' => $this->fields([
                'spend',
                'impressions',
                'reach',
                'clicks',
                'cpc',
                'actions',
            ]),
            'date_preset' => $datePreset,
        ]);

        $this->throwIfFailed($resp);
        $data = $this->unwrapData($resp);

        return $data[0] ?? [];
    }
}
