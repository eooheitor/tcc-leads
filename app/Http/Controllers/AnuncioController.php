<?php

namespace App\Http\Controllers;

use App\Services\AnuncioService;
use App\View\Grids\AnuncioGrid;

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
        return view('anuncios.index', compact('grid', 'anuncios'));

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Não foi possível buscar as anuncios.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
