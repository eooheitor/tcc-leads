<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Mensagem;
use App\Services\AdSetService;
use App\Services\CampanhaService;
use Illuminate\Http\Request;
use App\Services\HomeService;

class HomeController extends Controller
{
    public function index(Request $request, HomeService $homeService, CampanhaService $campanhaService, AdSetService $adSetService)
    {
        $clientesCount = Cliente::count();
        $mensagensCount = Mensagem::count();

        $days = (int) $request->input('days', 30);
        $campaignId = $request->input('campaign_id');
        $adSetId = $request->input('adset_id');

        $campanhas = $campanhaService->getCampanhas();
        $adSets = $adSetService->getAdSets([]);

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


        $lastCliente = Cliente::latest('created_at')->first();
        $lastClienteDiff = $lastCliente
            ? $lastCliente->created_at->diffForHumans()
            : 'Nenhum cliente cadastrado ainda';
        $lastClienteCode = $lastCliente
            ? sprintf('#CL-%04d', $lastCliente->id)
            : null;

        $lastMensagem = Mensagem::latest('created_at')->first();
        $lastMensagemDiff = $lastMensagem
            ? $lastMensagem->created_at->diffForHumans()
            : 'Nenhuma mensagem cadastrada ainda';

        $leadsMeta = 100; 
        $leadsMes = Cliente::whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth(),
        ])->count();

        $leadsPercent = $leadsMeta > 0
            ? min(100, (int) round($leadsMes / $leadsMeta * 100))
            : 0;

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
            // novos:
            'lastClienteDiff',
            'lastClienteCode',
            'lastMensagemDiff',
            'leadsMes',
            'leadsMeta',
            'leadsPercent',
        ));
    }

    public function teste()
    {
        return view('teste');
    }
}
