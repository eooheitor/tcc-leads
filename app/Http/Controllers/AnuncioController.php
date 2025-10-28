<?php

namespace App\Http\Controllers;

use App\Services\AnuncioService;
use App\View\Grids\AnuncioGrid;
use Illuminate\Http\Request;

class AnuncioController extends Controller
{
    protected $anuncioService;

    public function __construct(AnuncioService $anuncioService)
    {
        $this->anuncioService = $anuncioService;
    }

    public function index()
    {
        try {
        $anuncios = $this->anuncioService->getAnuncios();
        $anuncios = array_map(fn($item) => (object) $item, $anuncios);
        
        $grid = new AnuncioGrid($anuncios);
        $form = new \App\View\Forms\AnuncioForm($anuncio ?? null);
        return view('anuncios.index', compact('grid', 'anuncios', 'form'));

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Não foi possível buscar as anuncios.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $anuncio = $this->anuncioService->getAnuncioById($id);

            if (!$anuncio || empty($anuncio['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anúncio não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'anuncio' => $anuncio
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Ocorreu um erro ao buscar o anúncio. {$e->getMessage()}"
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            // Validação semelhante ao seu padrão
            $request->validate([
                'name'        => 'nullable|string|max:255',
                'status'      => 'nullable|string|in:ACTIVE,PAUSED,ARCHIVED,DELETED',
                'creative_id' => 'nullable|string', // ID de um Ad Creative existente
            ]);

            $payload = $request->only('name', 'status', 'creative_id');

            $result = $this->anuncioService->updateAnuncio($id, $payload);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anúncio atualizado com sucesso!',
                    'result'  => $result
                ]);
            }

            return redirect()->route('anuncios.index')->with('success', 'Anúncio atualizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors'  => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Ocorreu um erro ao atualizar. {$e->getMessage()}"
                ], 500);
            }

            return redirect()->back()->with('error', "Ocorreu um erro ao atualizar. {$e->getMessage()}");
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $result = $this->anuncioService->deleteAnuncio($id);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Anúncio excluído com sucesso!',
                    'result'  => $result
                ]);
            }

            return redirect()->route('anuncios.index')->with('success', 'Anúncio excluído com sucesso!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Ocorreu um erro ao excluir. {$e->getMessage()}"
                ], 500);
            }

            return redirect()->back()->with('error', "Ocorreu um erro ao excluir. {$e->getMessage()}");
        }
    }
}
