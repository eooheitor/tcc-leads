<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdSetService;
use App\Services\CampanhaService;
use App\View\Grids\AdSetGrid;
use Illuminate\Support\Facades\Log;
use App\Exceptions\FacebookApiException;
use App\Services\AnuncioService;
use App\View\Forms\AdSetForm;
use App\View\Forms\AnuncioForm;
use App\View\Grids\AnuncioGrid;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdSetController extends Controller
{
    public function __construct(
        protected AdSetService $adSetService,
        protected CampanhaService $campanhaService,
        protected AnuncioService $anuncioService,
    ) {}

    public function index()
    {
        try {
            $adsets = $this->adSetService->getAdSets();
            $adsets = array_map(fn($i) => (object) $i, $adsets);

            $campaignOptions = $this->mapCampaignOptions($this->campanhaService->getCampanhas());

            $grid = new AdSetGrid($adsets, $campaignOptions);
            $form = new \App\View\Forms\AdSetForm(null, $campaignOptions);

            return view('adsets.index', compact(
                'grid',
                'adsets',
                'form',
                'campaignOptions'
            ));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível carregar os conjuntos de anúncios.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('adsets.store payload', $request->all());
            $data = $this->validateCreate($request);

            if (in_array($data['optimization_goal'], ['CONVERSIONS', 'OFFSITE_CONVERSIONS'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Otimização por conversão ainda não está disponível neste ambiente. Escolha outra opção.',
                ], 422);
            }

            // ---------- 1) Dinheiro -> centavos ----------
            foreach (['daily_budget', 'bid_amount'] as $money) {
                if (!empty($data[$money])) {
                    $data[$money] = $this->toCents($data[$money]); // int em centavos
                } else {
                    $data[$money] = null;
                }
            }

            // Regra de lance + estratégia
            $strategy = $data['bid_strategy'] ?? 'LOWEST_COST_WITHOUT_CAP';

            if ($strategy === 'LOWEST_COST_WITHOUT_CAP') {
                // Estratégia sem limite → Meta NÃO aceita bid_amount
                $data['bid_amount'] = null;
            } elseif ($strategy === 'LOWEST_COST_WITH_BID_CAP') {
                // Com limite → precisa de bid_amount >= R$ 1,00 => 100 centavos
                if (empty($data['bid_amount']) || $data['bid_amount'] < 100) {
                    throw ValidationException::withMessages([
                        'bid_amount' => ['Informe um lance mínimo de R$ 1,00 quando usar limite de lance.'],
                    ]);
                }
            }

            // ---------- 2) Idades padrão ----------
            $data['age_min'] = (int)($data['age_min'] ?? 18);
            $data['age_max'] = (int)($data['age_max'] ?? 65);

            // ---------- 3) Ajustar optimization_goal com base na campanha ----------
            if (!empty($data['campaign_id'])) {
                try {
                    $campanha  = $this->campanhaService->getCampanhaById($data['campaign_id']);
                    $objective = $campanha['objective'] ?? null;

                    if ($objective) {
                        $data['optimization_goal'] = $this->mapOptimizationFromObjective(
                            $objective,
                            $data['optimization_goal'] ?? null
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('adsets.store: falha ao obter campanha para mapear optimization_goal', [
                        'campaign_id' => $data['campaign_id'],
                        'error'       => $e->getMessage(),
                    ]);
                }
            }

            // ---------- 4) Cria o conjunto ----------
            $adset = $this->adSetService->createAdSet($data);

            return response()->json([
                'success' => true,
                'message' => 'Conjunto criado com sucesso!',
                'adset'   => $adset,
                'adset_id' => $adset['id'] ?? null,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (FacebookApiException $e) {
            Log::error('adsets.store: facebook api error', ['raw' => $e->getRaw()]);
            return response()->json([
                'success' => false,
                'message' => $e->getUserMessage() ?? 'Falha na API do Facebook.',
            ], $e->getStatus() ?: 400);
        } catch (\Throwable $e) {
            Log::error('adsets.store: fatal', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => "Erro ao criar o conjunto. {$e->getMessage()}",
            ], 500);
        }
    }

    private function mapOptimizationFromObjective(string $objective, ?string $fallback = null): string
    {
        $objective = strtoupper($objective);

        return match ($objective) {
            'OUTCOME_LEADS'         => 'LEAD_GENERATION',
            'OUTCOME_SALES'         => 'OFFSITE_CONVERSIONS',
            'OUTCOME_TRAFFIC'       => 'LINK_CLICKS',
            'OUTCOME_ENGAGEMENT'    => 'IMPRESSIONS',
            'OUTCOME_AWARENESS'     => 'REACH',
            'OUTCOME_APP_PROMOTION' => 'LINK_CLICKS',
            default                 => $fallback ?: 'LINK_CLICKS',
        };
    }

    public function formCreate()
    {
        $campaignOptions = $this->mapCampaignOptions($this->campanhaService->getCampanhas());

        $form = new \App\View\Forms\AdSetForm(null, $campaignOptions);

        // formulário de anúncios vazio (já preparado)
        $anuncioForm   = new \App\View\Forms\AnuncioForm();
        $adsFormHtml   = $anuncioForm->render();

        // grid de anúncios vazio
        $adsGridHtml = view('adsets.partials.ads_table', [
            'anuncios' => [],
        ])->render();

        return response()->json([
            'success'        => true,
            'title'          => $form->getTitle(),
            'method'         => $form->getMethod(),
            'action'         => route('adsets.store'),
            'multipart'      => false,
            'fields_html'    => $form->render(),
            'has_adset'      => false,
            'adset_id'       => null,
            'ads_form_html'  => $adsFormHtml,
            'ads_grid_html'  => $adsGridHtml,
            'has_adset'     => false, // ou true no edit, etc.
        ]);
    }


    public function formEdit(string $id)
    {
        try {
            $adset = $this->adSetService->getAdSetById($id);

            foreach (['start_time', 'end_time', 'stop_time'] as $field) {
                if (!empty($adset[$field])) {
                    try {
                        $adset[$field] = Carbon::parse($adset[$field])->format('Y-m-d');
                    } catch (\Throwable $e) {
                        $adset[$field] = null;
                    }
                }
            }

            $campaigns       = $this->campanhaService->getCampanhas();
            $campaignOptions = collect($campaigns)->pluck('name', 'id')->toArray();

            $form = new \App\View\Forms\AdSetForm($adset, $campaignOptions);

            // form de anúncio (já podemos passar adset_id se quiser)
            $anuncioForm = new \App\View\Forms\AnuncioForm();
            $adsFormHtml = $anuncioForm->render();

            // anúncios desse conjunto (se der erro na API, só devolve lista vazia)
            $anuncios = [];
            try {
                $anuncios = $this->anuncioService->getAnunciosByAdset($id);
            } catch (\Throwable $e) {
                Log::warning('formEdit anuncios error', [
                    'adset_id' => $id,
                    'msg'      => $e->getMessage(),
                ]);
                $anuncios = [];
            }

            // dd($anuncios);

            $adsGridHtml = view('adsets.partials.ads_table', [
                'anuncios' => $anuncios,
            ])->render();

            return response()->json([
                'success'        => true,
                'title'          => 'Editar Conjunto de Anúncio',
                'method'         => 'PUT',
                'action'         => route('adsets.update', $id),
                'multipart'      => false,
                'fields_html'    => $form->render(),
                'ads_form_html'  => $adsFormHtml,
                'ads_grid_html'  => $adsGridHtml,
                'has_adset'      => true,
                'adset_id'       => $id,
            ]);
        } catch (\Throwable $e) {
            Log::error('formEdit error', ['ex' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar formulário: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function adsGrid(string $adsetId)
    {
        try {
            $anuncios = $this->anuncioService->getAnunciosByAdset($adsetId);

            return response()->json([
                'success' => true,
                'html'    => view('adsets.partials.ads_table', [
                    'anuncios' => $anuncios,
                ])->render(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AdSetController@adsGrid error', [
                'adset_id' => $adsetId,
                'msg'      => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao recarregar os anúncios do conjunto.',
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = $this->validateUpdate($request);
            foreach (['daily_budget', 'bid_amount'] as $money) {
                if (!empty($data[$money])) $data[$money] = $this->toCents($data[$money]);
            }

            $result = $this->adSetService->updateAdSet($id, $data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conjunto atualizado com sucesso!',
                    'result'  => $result,
                ]);
            }

            return redirect()->route('adsets.index')->with('success', 'Conjunto atualizado com sucesso!');
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getUserMessage() ?? 'Falha na API do Facebook.',
                'error'   => $e->getMessage(),
            ], $e->getStatus() ?? 400);
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Erro ao atualizar o conjunto. {$e->getMessage()}",
                ], 500);
            }
            return redirect()->back()->with('error', "Erro ao atualizar o conjunto. {$e->getMessage()}");
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $ok = $this->adSetService->deleteAdSet($id);

            if ($request->ajax()) {
                return response()->json([
                    'success' => (bool)$ok,
                    'message' => $ok ? 'Conjunto excluído com sucesso!' : 'Não foi possível excluir o conjunto.',
                ], $ok ? 200 : 500);
            }

            return redirect()->route('adsets.index')->with(
                $ok ? 'success' : 'error',
                $ok ? 'Conjunto excluído com sucesso!' : 'Não foi possível excluir o conjunto.'
            );
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Erro ao excluir o conjunto. {$e->getMessage()}",
                ], 500);
            }
            return redirect()->back()->with('error', "Erro ao excluir o conjunto. {$e->getMessage()}");
        }
    }

    private function mapCampaignOptions(array $campanhas): array
    {
        $out = [];
        foreach ($campanhas as $c) {
            $id = $c['id'] ?? null;
            if (!$id) continue;
            $out[(string)$id] = $c['name'] ?? (string)$id;
        }
        return $out;
    }

    protected function validateCreate(Request $request): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'campaign_id'   => ['required', 'string'],
            'status'        => ['required', Rule::in(['ACTIVE', 'PAUSED'])],
            'optimization_goal' => ['required', 'in:REACH,IMPRESSIONS,LINK_CLICKS,LEAD_GENERATION'],
            'billing_event' => ['required', Rule::in(['IMPRESSIONS', 'CLICKS', 'LEAD'])],

            'daily_budget'  => ['nullable', 'regex:/^\d{1,3}(\.\d{3})*,\d{2}$|^\d+,\d{2}$/'],

            'bid_strategy'  => ['nullable', Rule::in([
                'LOWEST_COST_WITHOUT_CAP',
                'LOWEST_COST_WITH_BID_CAP',
            ])],
            'bid_amount'    => ['nullable', 'regex:/^\d{1,3}(\.\d{3})*,\d{2}$|^\d+,\d{2}$/'],

            'age_min'       => ['nullable', 'integer', 'min:13'],
            'age_max'       => ['nullable', 'integer', 'min:13'],
            'country'       => ['nullable', 'string', 'size:2'],
            'start_time'    => ['nullable', 'date'],
            'end_time'      => ['nullable', 'date', 'after_or_equal:start_time'],
        ], [
            'daily_budget.regex' => 'Informe o orçamento diário em reais (ex.: 25,00 ou 1.234,56).',
            'bid_amount.regex'   => 'Informe o lance em reais (ex.: 5,00 ou 1.234,56).',
        ]);
    }

    private function validateUpdate(Request $request): array
    {
        return $request->validate([
            'name'              => ['nullable', 'string', 'max:255'],
            'status'            => ['nullable', 'in:ACTIVE,PAUSED,ARCHIVED,DELETED'],

            'optimization_goal' => ['nullable', 'in:LINK_CLICKS,IMPRESSIONS,CONVERSIONS,LEAD_GENERATION,OFFSITE_CONVERSIONS'],
            'billing_event'     => ['nullable', 'in:IMPRESSIONS,CLICKS,LEAD'],

            'daily_budget'      => ['nullable', 'regex:/^\d{1,3}(\.\d{3})*,\d{2}$|^\d+,\d{2}$/'],
            'bid_amount'        => ['nullable', 'regex:/^\d{1,3}(\.\d{3})*,\d{2}$|^\d+,\d{2}$/'],

            'start_time'        => ['nullable', 'date'],
            'end_time'          => ['nullable', 'date', 'after_or_equal:start_time'],
        ]);
    }

    protected function normalizeToForm($adset)
    {
        $norm = [];
        $norm['id']                = (string)($adset['id'] ?? '');
        $norm['name']              = $adset['name'] ?? '';
        $norm['status']            = $adset['status'] ?? 'PAUSED';
        $norm['campaign_id']       = $adset['campaign_id'] ?? '';

        $norm['optimization_goal'] = $adset['optimization_goal'] ?? 'LINK_CLICKS';
        $norm['billing_event']     = $adset['billing_event']     ?? 'IMPRESSIONS';

        $toBrl = function ($cents) {
            if ($cents === null || $cents === '' || !is_numeric($cents)) return '';
            return number_format(((int)$cents) / 100, 2, ',', '.');
        };
        $norm['daily_budget'] = isset($adset['daily_budget']) ? $toBrl($adset['daily_budget']) : '';
        $norm['bid_amount']   = isset($adset['bid_amount'])   ? $toBrl($adset['bid_amount'])   : '';

        $countries = data_get($adset, 'targeting.geo_locations.countries', []);
        $norm['country']  = is_array($countries) && count($countries) ? $countries[0] : 'BR';
        $norm['age_min']  = (int)($adset['targeting']['age_min'] ?? 18);
        $norm['age_max']  = (int)($adset['targeting']['age_max'] ?? 65);

        $fmtDate = function ($val) {
            if (!$val) return '';
            try {
                return \Carbon\Carbon::parse($val)->timezone(config('app.timezone'))->format('Y-m-d');
            } catch (\Throwable $e) {
                return '';
            }
        };
        $norm['start_time'] = $fmtDate($adset['start_time'] ?? null);
        $norm['end_time']   = $fmtDate($adset['end_time']   ?? null);
        return $norm;
    }
}
