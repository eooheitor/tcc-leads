<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AnuncioService;
use App\Services\AdSetService;
use App\Exceptions\FacebookApiException;

class AnuncioController extends Controller
{
    public function __construct(
        protected AnuncioService $anuncioService,
        protected AdSetService   $adSetService
    ) {}


    public function store(Request $request)
    {
        try {
            $data = $this->validateCreate($request);

            $image = $request->file('images.0') ?? ($request->file('images')[0] ?? null);
            $anuncio = $this->anuncioService->createFromForm($data, $image);

            $anuncios = $this->anuncioService->getAnunciosByAdset($data['adset_id']);

            $adsGridHtml = view('adsets.partials.ads_table', [
                'anuncios' => $anuncios,
            ])->render();

            return response()->json([
                'success'       => true,
                'message'       => 'Anúncio criado com sucesso!',
                'anuncio'       => $anuncio,
                'ads_grid_html' => $adsGridHtml,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('anuncios.store fatal', ['ex' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível criar o anúncio. ' . $e->getMessage(),
            ], 500);
        }
    }


    /** EDITAR (dados + opções para modal) */
    public function edit(string $id)
    {
        try {
            $anuncio = $this->anuncioService->getAnuncioById($id);
            if (empty($anuncio['id'])) {
                return response()->json(['success' => false, 'message' => 'Anúncio não encontrado.'], 404);
            }

            $adsetOptions = $this->mapAdsetOptions($this->adSetService->getAdSets());

            // garante que o adset atual esteja nas opções
            if (!empty($anuncio['adset_id']) && !isset($adsetOptions[$anuncio['adset_id']])) {
                try {
                    $as = $this->adSetService->getAdSetById($anuncio['adset_id']);
                    if (!empty($as['id'])) {
                        $adsetOptions[$as['id']] = $as['name'] ?? $as['id'];
                    }
                } catch (\Throwable) {
                }
            }

            return response()->json([
                'success'       => true,
                'message'       => 'Anúncio carregado.',
                'anuncio'       => $anuncio,
                'adset_options' => $adsetOptions,
                'mode'          => 'edit',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Ocorreu um erro ao buscar o anúncio. {$e->getMessage()}",
            ], 500);
        }
    }

    /**
     * ATUALIZAR: altera nome/status e permite trocar o creative via `creative_id`.
     * (Se quiser aceitar upload aqui também, expomos um método público no service para criar o creative.)
     */
    public function update(Request $request, string $id)
    {
        try {
            $data = $this->validateUpdate($request);
            $image = $request->file('images.0') ?? ($request->file('images')[0] ?? null);
            $anuncio = $this->anuncioService->updateFromForm($id, $data, $image);
            $adsetId = $anuncio['adset_id'] ?? $data['adset_id'] ?? null;
            $anuncios = [];

            if ($adsetId) {
                $anuncios = $this->anuncioService->getAnunciosByAdset($data['adset_id']);
            }

            $adsGridHtml = view('adsets.partials.ads_table', [
                'anuncios' => $anuncios,
            ])->render();

            return response()->json([
                'success'       => true,
                'message'       => 'Anúncio atualizado com sucesso!',
                'anuncio'       => $anuncio,
                'ads_grid_html' => $adsGridHtml,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('anuncios.update fatal', ['ex' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível atualizar o anúncio. ' . $e->getMessage(),
            ], 500);
        }
    }

    /** DELETAR */
    public function destroy(Request $request, string $id)
    {
        try {
            // adset atual (pra recarregar o grid depois)
            $adsetId = $request->input('adset_id');

            // chama a Meta pra excluir o anúncio
            $this->anuncioService->deleteAd($id);

            // recarrega o grid daquele conjunto, se tiver adset_id
            $adsGridHtml = null;
            if ($adsetId) {
                $anuncios = $this->anuncioService->getAnunciosByAdset($adsetId);

                $adsGridHtml = view('adsets.partials.ads_table', [
                    'anuncios' => $anuncios,
                ])->render();
            }

            return response()->json([
                'success'       => true,
                'message'       => 'Anúncio excluído com sucesso!',
                'ads_grid_html' => $adsGridHtml,
            ]);
        } catch (\Throwable $e) {
            Log::error('anuncios.destroy fatal', [
                'ad_id' => $id,
                'ex'    => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível excluir o anúncio. ' . $e->getMessage(),
            ], 500);
        }
    }

    /** ===== Helpers ===== */

    /** Mapa id => nome para o select de AdSets */
    private function mapAdsetOptions(array $adsets): array
    {
        $out = [];
        foreach ($adsets as $as) {
            $id = $as['id'] ?? null;
            if (!$id) continue;
            $name = $as['name'] ?? (string)$id;
            $out[(string)$id] = $name;
        }
        return $out;
    }

    /** ===== Validações ===== */

    private function validateCreate(Request $request): array
    {
        return $request->validate([
            'adset_id'      => ['required', 'string'],
            'ad_name'          => ['required', 'string', 'max:255'],
            'status'        => ['required', 'in:ACTIVE,PAUSED,ARCHIVED,DELETED'],

            'link_url'      => ['required', 'url'],
            'primary_text'  => ['nullable', 'string'],
            'headline'      => ['nullable', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:255'],
            'call_to_action' => ['nullable', 'string'],

            'images.*'      => ['nullable', 'image'],
        ], [
            'name.required'      => 'Informe o nome do anúncio.',
            'adset_id.required'  => 'Conjunto inválido.',
            'link_url.required'  => 'Informe a URL de destino.',
            'link_url.url'       => 'Informe uma URL de destino válida.',
        ]);
    }

    protected function validateUpdate(Request $request): array
    {
        return $request->validate([
            'ad_name'       => ['sometimes', 'string', 'max:255'],
            'status'        => ['sometimes', 'in:ACTIVE,PAUSED'],
            'adset_id'      => ['sometimes', 'string'],
            'link_url'      => ['sometimes', 'nullable', 'url'],
            'primary_text'  => ['sometimes', 'nullable', 'string'],
            'headline'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'description'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'call_to_action' => ['sometimes', 'nullable', 'string'],
            // imagem OPCIONAL no update
            'images.*'      => ['sometimes', 'file', 'image', 'max:4096'],
        ]);
    }
}
