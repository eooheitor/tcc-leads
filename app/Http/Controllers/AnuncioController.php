<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AnuncioService;
use App\Services\AdSetService;
use App\View\Grids\AnuncioGrid;
use App\Exceptions\FacebookApiException;

class AnuncioController extends Controller
{
    public function __construct(
        protected AnuncioService $anuncioService,
        protected AdSetService   $adSetService
    ) {}

    public function index()
    {
        try {
            $anuncios = $this->anuncioService->getAnuncios();
            $anuncios = array_map(fn($i) => (object) $i, $anuncios);

            // opções para o select de AdSets (id => name)
            $adsets       = $this->adSetService->getAdSets();
            $adsetOptions = $this->mapAdsetOptions($adsets);

            $grid = new AnuncioGrid($anuncios);
            // caso queira usar no grid (exibir nome do AdSet):
            $grid->setFormData(['adset_map' => $adsetOptions]);

            $form = new \App\View\Forms\AnuncioForm(null, $adsetOptions);

            return view('anuncios.index', compact('grid', 'anuncios', 'form'));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível carregar os anúncios/conjuntos.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /** CRIAR anúncio (aceita upload de imagem para criar o creative) */
    public function store(Request $request)
    {
        try {
            $data = $this->validateCreate($request);

            $filePath = null;
            if ($request->hasFile('creative_file')) {
                $file = $request->file('creative_file');
                // usa caminho temporário real do PHP (sem salvar)
                $filePath = $file->getRealPath();
            }

            $ad = $this->anuncioService->createAnuncio($data, $filePath);

            return response()->json([
                'success' => true,
                'message' => 'Anúncio criado com sucesso!',
                'anuncio' => $ad,
            ], 201);
        } catch (FacebookApiException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getUserMessage() ?? 'Falha na API do Facebook.',
                'error'   => $e->getMessage(),
            ], $e->getStatus() ?? 400);
        } catch (\Throwable $e) {
            Log::error('Create ad fatal', ['ex' => $e]);
            return response()->json([
                'success' => false,
                'message' => "Erro ao criar o anúncio. {$e->getMessage()}",
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

            // OBS: upload de imagem no update não está habilitado por default.
            // Se precisar, podemos expor um método público no service para gerar creative e setar $data['creative_id'].

            $payload = array_filter([
                'name'         => $data['name']        ?? null,
                'status'       => $data['status']      ?? null,
                'creative_id'  => $data['creative_id'] ?? null,
            ], fn($v) => !is_null($v) && $v !== '');

            $result = $this->anuncioService->updateAnuncio($id, $payload);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anúncio atualizado com sucesso!',
                    'result'  => $result,
                ]);
            }

            return redirect()->route('anuncios.index')->with('success', 'Anúncio atualizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (FacebookApiException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getUserMessage() ?? 'Falha na API do Facebook.',
                    'error'   => $e->getMessage(),
                ], $e->getStatus() ?? 400);
            }
            return redirect()->back()->with('error', $e->getUserMessage() ?? $e->getMessage());
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Ocorreu um erro ao atualizar. {$e->getMessage()}",
                ], 500);
            }
            return redirect()->back()->with('error', "Ocorreu um erro ao atualizar. {$e->getMessage()}");
        }
    }

    /** DELETAR */
    public function destroy(Request $request, string $id)
    {
        try {
            $ok = $this->anuncioService->deleteAnuncio($id);

            if ($request->ajax()) {
                return response()->json([
                    'success' => (bool) $ok,
                    'message' => $ok ? 'Anúncio excluído com sucesso!' : 'Não foi possível excluir o anúncio.',
                ], $ok ? 200 : 500);
            }

            return redirect()
                ->route('anuncios.index')
                ->with($ok ? 'success' : 'error', $ok ? 'Anúncio excluído com sucesso!' : 'Não foi possível excluir o anúncio.');
        } catch (\Throwable $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Ocorreu um erro ao excluir. {$e->getMessage()}",
                ], 500);
            }
            return redirect()->back()->with('error', "Ocorreu um erro ao excluir. {$e->getMessage()}");
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
            'name'          => ['required', 'string', 'max:255'],
            'adset_id'      => ['required', 'string'],
            'status'        => ['nullable', 'in:ACTIVE,PAUSED,ARCHIVED,DELETED'],

            // Criativo: ou arquivo (upload) ou creative_id (fallback)
            'creative_file' => ['nullable', 'file', 'image', 'max:8192'], // 8MB
            'creative_id'   => ['nullable', 'string'],
        ], [
            'adset_id.required' => 'Selecione um Conjunto de Anúncios.',
        ]);
    }

    private function validateUpdate(Request $request): array
    {
        return $request->validate([
            'name'        => ['nullable', 'string', 'max:255'],
            'status'      => ['nullable', 'in:ACTIVE,PAUSED,ARCHIVED,DELETED'],
            'creative_id' => ['nullable', 'string'],
        ]);
    }
}
