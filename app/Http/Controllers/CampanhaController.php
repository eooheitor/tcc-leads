<?php

namespace App\Http\Controllers;

use App\Services\CampanhaService;
use App\View\Grids\CampanhaGrid;
use App\Exceptions\FacebookApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CampanhaController extends Controller
{
    public function __construct(protected CampanhaService $campanhaService) {}

    public function index()
    {
        try {
            $campanhas = $this->campanhaService->getCampanhas();
            $campanhas = array_map(fn($i) => (object) $i, $campanhas);

            $grid = (new CampanhaGrid($campanhas))
                ->setRouteName('campanhas');

            $form = new \App\View\Forms\CampanhaForm();

            return view('campanhas.index', compact('grid', 'campanhas', 'form'));
        } catch (FacebookApiException $e) {
            return $this->handleFacebookException($e);
        } catch (\Throwable $e) {
            return $this->jsonError('Não foi possível buscar as campanhas.', 500, [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function formCreate()
    {
        try {
            $form = new \App\View\Forms\CampanhaForm(); 
            return response()->json([
                'success'     => true,
                'title'       => 'Nova Campanha',
                'method'      => 'POST',
                'action'      => route('campanhas.store'),
                'multipart'   => false,
                'fields_html' => $form->render(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar formulário: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function formEdit(string $id)
    {
        try {
            $raw = $this->campanhaService->getCampanhaById($id);
            if (!$raw || empty($raw['id'])) {
                return response()->json(['success' => false, 'message' => 'Campanha não encontrada.'], 404);
            }

            $camp = $raw;
            $camp['daily_budget'] = isset($camp['daily_budget'])
                ? $this->fromCentsToBrl($camp['daily_budget'])
                : null;

            $camp['buying_type'] = $camp['buying_type'] ?? 'AUCTION';

            $cats = $camp['special_ad_categories'] ?? [];
            $selectedCat = (is_array($cats) && count($cats)) ? $cats[0] : 'NONE';
            $camp['special_ad_categories[]']   = $selectedCat;
            $camp['special_ad_categories_select'] = $selectedCat;

            $campObj = (object) $camp;

            $form = new \App\View\Forms\CampanhaForm($campObj);

            return response()->json([
                'success'     => true,
                'title'       => 'Editar Campanha',
                'method'      => 'PUT',
                'action'      => url('campanhas/' . $camp['id']),
                'multipart'   => false,
                'fields_html' => $form->render(),
            ]);
        } catch (\Throwable $e) {
            Log::error('campanhas.formEdit error', ['ex' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar formulário: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $this->validateCreate($request);

            $data['daily_budget'] = $this->toCents($request->input('daily_budget'));

            $cat = $this->extractSingleCategory($request, 'special_ad_categories');
            if ($cat && $cat !== 'NONE') {
                $data['special_ad_categories'] = [$cat];
                $data['special_ad_category_country'] = 'BR';
            } else {
                unset($data['special_ad_categories'], $data['special_ad_category_country']);
            }

            $campanha = $this->campanhaService->createCampanha($data);

            return $this->jsonSuccess(['campanha' => $campanha], 'Campanha criada com sucesso!', 201);
        } catch (FacebookApiException $e) {
            return $this->handleFacebookException($e);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonError('Dados inválidos.', 422, ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            return $this->jsonError('Erro interno ao criar a campanha.', 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = $this->validateUpdate($request);

            if ($request->filled('daily_budget')) {
                $data['daily_budget'] = $this->toCents($request->input('daily_budget'));
            }

            // NÃO enviar campos imutáveis
            unset($data['special_ad_categories'], $data['special_ad_category_country'], $data['buying_type'], $data['objective']);

            $campanha = $this->campanhaService->updateCampanha($id, $data);

            return $this->jsonSuccess(['campanha' => $campanha], 'Campanha atualizada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonError('Dados inválidos.', 422, ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            return $this->jsonError("Ocorreu um erro ao atualizar a campanha. {$e->getMessage()}", 500);
        }
    }

    /** DESTROY */
    public function destroy(string $id)
    {
        try {
            $deleted = $this->campanhaService->deleteCampanha($id);
            return $deleted
                ? $this->jsonSuccess([], 'Campanha deletada com sucesso.')
                : $this->jsonError('Não foi possível deletar a campanha.', 500);
        } catch (\Throwable $e) {
            return $this->jsonError("Ocorreu um erro ao deletar a campanha. {$e->getMessage()}", 500);
        }
    }

    protected function validateCreate(Request $request): array
    {
        return $this->validateUsing($request, [
            'name'        => ['required', 'string', 'max:255'],
            'buying_type' => ['required', 'in:AUCTION,RESERVED'],
            'objective'   => ['required', 'in:OUTCOME_LEADS,OUTCOME_SALES,OUTCOME_ENGAGEMENT,OUTCOME_AWARENESS,OUTCOME_TRAFFIC,OUTCOME_APP_PROMOTION'],
            'daily_budget' => ['required', 'regex:/^\d{1,3}(\.\d{3})*,\d{2}$|^\d+,\d{2}$/'],
            'special_ad_categories' => ['nullable'],
        ], [
            'daily_budget.regex' => 'Informe o orçamento diário em reais (ex.: 25,00 ou 1.234,56).',
        ]);
    }

    protected function validateUpdate(Request $request): array
    {
        return $this->validateUsing($request, [
            'name'        => ['sometimes', 'string', 'max:255'],
            'buying_type' => ['sometimes', 'in:AUCTION,RESERVED'],
            'objective'   => ['sometimes', 'in:OUTCOME_LEADS,OUTCOME_SALES,OUTCOME_ENGAGEMENT,OUTCOME_AWARENESS,OUTCOME_TRAFFIC,OUTCOME_APP_PROMOTION'],
            'daily_budget' => ['sometimes', 'regex:/^\d{1,3}(\.\d{3})*,\d{2}$|^\d+,\d{2}$/'],
            'special_ad_categories' => ['sometimes'],
        ], [
            'daily_budget.regex' => 'Informe o orçamento diário em reais (ex.: 25,00 ou 1.234,56).',
        ]);
    }
}
