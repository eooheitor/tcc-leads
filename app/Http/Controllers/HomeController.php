<?php

namespace App\Http\Controllers;

use App\Services\AdSetService;
use App\Services\CampanhaService;
use Illuminate\Http\Request;
use App\Services\HomeService;

class HomeController extends Controller
{
    public function index(Request $request, HomeService $homeService, CampanhaService $campanhaService, AdSetService $adSetService)
    {
        $clientesCount = \App\Models\Cliente::count();
        $mensagensCount = \App\Models\Mensagem::count();

        $days = (int) $request->input('days', 30);
        $campaignId = $request->input('campaign_id');
        $adSetId = $request->input('adset_id');

        // carrega listas para os filtros do dropdown
        $campanhas = $campanhaService->getCampanhas();
        $adSets = $adSetService->getAdSets([
            // 'filtering' => [
            //     [
            //         'field' => 'effective_status',
            //         'operator' => 'IN',
            //         'value' => ['ACTIVE', 'PAUSED'],
            //     ]
            // ]
        ]);

        $insights = $homeService->getInsights([
            'days' => $days,
            'campaign_id' => $campaignId,
            'adset_id' => $adSetId,
        ]);

        // trata valores
        $spend        = $insights['spend']        ?? 0;
        $impressions  = $insights['impressions']  ?? 0;
        $reach        = $insights['reach']        ?? 0;
        $clicks       = $insights['clicks']       ?? 0;
        $cpc          = $insights['cpc']          ?? 0;
        $leads        = collect($insights['actions'] ?? [])
            ->firstWhere('action_type', 'lead')['value'] ?? 0;

        return view('home', compact(
            'clientesCount',
            'mensagensCount',
            'spend',
            'impressions',
            'reach',
            'clicks',
            'cpc',
            'leads',
            'days',
            'campanhas',
            'adSets',
            'campaignId',
            'adSetId',
        ));
    }

    public function teste()
    {
        return view('teste');
    }
}
