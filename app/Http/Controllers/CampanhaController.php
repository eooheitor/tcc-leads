<?php

namespace App\Http\Controllers;

use App\Services\CampanhaService;
use App\View\Grids\CampanhaGrid;

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
        return view('campanhas.index', compact('grid', 'campanhas'));

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Não foi possível buscar as campanhas.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
