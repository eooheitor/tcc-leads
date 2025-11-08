<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdSetService;
use App\Services\CampanhaService;
use App\View\Grids\AdSetGrid;
use Illuminate\Support\Facades\Log;
use App\Exceptions\FacebookApiException;
use Carbon\Carbon;

class AdSetController extends Controller
{
    public function __construct(
        protected AdSetService $adSetService,
        protected CampanhaService $campanhaService
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
            $data = $this->validateCreate($request);

            foreach (['daily_budget', 'bid_amount'] as $money) {
                if (!empty($data[$money])) $data[$money] = $this->toCents($data[$money]);
            }
            $data['age_min'] = (int)($data['age_min'] ?? 18);
            $data['age_max'] = (int)($data['age_max'] ?? 65);
            $adset = $this->adSetService->createAdSet($data);

            return response()->json([
                'success' => true,
                'message' => 'Conjunto criado com sucesso!',
                'adset'   => $adset,
            ], 201);
        } catch (FacebookApiException $e) {
            Log::error('adsets.store: facebook api error', ['raw' => $e->getRaw()]);
            return response()->json([
                'success' => false,
                'message' => $e->getUserMessage() ?? 'Falha na API do Facebook.',
            ], $e->getStatus() ?: 400);
        } catch (FacebookApiException $e) {
            Log::error('adsets.store: facebook api error', ['raw' => $e->getRaw()]);
            return response()->json([
                'success' => false,
                'message' => $e->getUserMessage() ?? 'Falha na API do Facebook.',
            ], $e->getStatus() ?: 400);
        } catch (\Throwable $e) {
            Log::error('adsets.store: fatal', ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => "Erro ao criar o conjunto. {$e->getMessage()}",
            ], 500);
        }
    }

    public function formCreate()
    {
        $campaignOptions = $this->mapCampaignOptions($this->campanhaService->getCampanhas());

        $form = new \App\View\Forms\AdSetForm(null, $campaignOptions);
        return response()->json([
            'success'     => true,
            'title'       => $form->getTitle(),
            'method'      => $form->getMethod(),
            'action'      => route('adsets.store'),
            'multipart'   => false,
            'fields_html' => $form->render(),
        ]);
    }

    public function formEdit(string $id)
    {
        try {
            $raw = $this->adSetService->getAdSetById($id);
            if (!$raw || empty($raw['id'])) {
                return response()->json(['success' => false, 'message' => 'Conjunto não encontrado.'], 404);
            }

            $toBrl = function ($cents) {
                if ($cents === null || $cents === '' || !is_numeric($cents)) return '';
                return number_format(((int)$cents) / 100, 2, ',', '.');
            };
            $fmtDate = function ($val) {
                if (!$val) return '';
                try {
                    return \Carbon\Carbon::parse($val)->timezone(config('app.timezone'))->format('Y-m-d');
                } catch (\Throwable $e) {
                    return '';
                }
            };

            $adset = (object)[
                'id'                => (string)($raw['id'] ?? ''),
                'name'              => $raw['name'] ?? '',
                'status'            => $raw['status'] ?? 'PAUSED',
                'campaign_id'       => $raw['campaign_id'] ?? '',
                'optimization_goal' => $raw['optimization_goal'] ?? 'LINK_CLICKS',
                'billing_event'     => $raw['billing_event']     ?? 'IMPRESSIONS',
                'daily_budget'      => isset($raw['daily_budget']) ? $toBrl($raw['daily_budget']) : '',
                'bid_amount'        => isset($raw['bid_amount'])   ? $toBrl($raw['bid_amount'])   : '',
                'age_min'           => (int)($raw['targeting']['age_min'] ?? 18),
                'age_max'           => (int)($raw['targeting']['age_max'] ?? 65),
                'country'           => data_get($raw, 'targeting.geo_locations.countries.0', 'BR'),
                'start_time'        => $fmtDate($raw['start_time'] ?? null),
                'end_time'          => $fmtDate($raw['end_time']   ?? null),
            ];

            $campaigns        = $this->campanhaService->getCampanhas();
            $campaignOptions  = collect($campaigns)->pluck('name', 'id')->toArray();
            if ($adset->campaign_id && !isset($campaignOptions[$adset->campaign_id])) {
                $camp = $this->campanhaService->getCampanhaById($adset->campaign_id);
                if (!empty($camp['id'])) $campaignOptions[$camp['id']] = $camp['name'] ?? $camp['id'];
            }

            $form = new \App\View\Forms\AdSetForm($adset, $campaignOptions);

            return response()->json([
                'success'      => true,
                'title'        => 'Editar Conjunto de Anúncio',
                'method'       => 'PUT',
                'action'       => url('adsets/' . $adset->id),
                'multipart'    => false,
                'fields_html'  => $form->render(),
            ]);
        } catch (\Throwable $e) {
            Log::error('formEdit error', ['ex' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar formulário: ' . $e->getMessage(),
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

    private function validateCreate(Request $r): array
    {
        return $r->validate([
            'name'              => ['required', 'string', 'max:255'],
            'campaign_id'       => ['required', 'string'],
            'status'            => ['nullable', 'in:ACTIVE,PAUSED,ARCHIVED,DELETED'],
            'optimization_goal' => ['nullable', 'string'], // service ajusta p/ LEAD_GENERATION se objetivo for leads
            'billing_event'     => ['nullable', 'string'],
            'daily_budget'      => ['nullable'], // R$ → será convertido no store()
            'bid_amount'        => ['nullable'],
            'age_min'           => ['nullable', 'integer', 'min:13', 'max:65'],
            'age_max'           => ['nullable', 'integer', 'min:13', 'max:65'],
            'country'           => ['nullable', 'string', 'size:2'],
            'start_time'        => ['nullable', 'date'],
            'end_time'          => ['nullable', 'date', 'after_or_equal:start_time'],
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
