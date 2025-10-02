<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $clientesCount = \App\Models\Cliente::count();
        $mensagensCount = \App\Models\Mensagem::count();
        return view('home', compact('clientesCount', 'mensagensCount'));
    }
}
