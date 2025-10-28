<?php

namespace App\Http\Controllers;

use App\Services\CampanhaService;
use App\View\Grids\CampanhaGrid;
use Illuminate\Http\Request;

class CampanhaController extends Controller
{
    protected $campanhaService;

    public function __construct(CampanhaService $campanhaService)
    {
        $this->campanhaService = $campanhaService;
    }

    public function index()
    {
        try {
            $campanhas = $this->campanhaService->getCampanhas();
            $campanhas = array_map(fn($item) => (object) $item, $campanhas);

            $grid = new CampanhaGrid($campanhas);
            $form = new \App\View\Forms\CampanhaForm($campanha ?? null);
            return view('campanhas.index', compact('grid', 'campanhas', 'form'));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Não foi possível buscar as campanhas.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit(string $id)
    {
        try {
            $campanha = $this->campanhaService->getCampanhaById($id);

            if (!$campanha || empty($campanha['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campanha não encontrada.'
                ], 404);
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Campanha carregada com sucesso.',
                'campanha' => $campanha
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Ocorreu um erro ao buscar a campanha. {$e->getMessage()}"
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = $request->only(['name', 'status', 'daily_budget', 'lifetime_budget']);
            $campanha = $this->campanhaService->updateCampanha($id, $data);

            return response()->json([
                'success'  => true,
                'message'  => 'Campanha atualizada com sucesso!',
                'campanha' => $campanha
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Ocorreu um erro ao atualizar a campanha. {$e->getMessage()}"
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $deleted = $this->campanhaService->deleteCampanha($id);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Campanha deletada com sucesso.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível deletar a campanha.'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Ocorreu um erro ao deletar a campanha. {$e->getMessage()}"
            ], 500);
        }
    }
}
