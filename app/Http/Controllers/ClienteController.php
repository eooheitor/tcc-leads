<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Mensagem;
use App\View\Grids\ClienteGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::with('mensagem')->orderBy('id')->paginate(10);
        $mensagens = Mensagem::orderBy('id')->get();

        $grid = new ClienteGrid($clientes, $mensagens);
        $form = new \App\View\Forms\ClienteForm(null, $mensagens ?? null);

        return view('clientes.index', compact('grid', 'clientes', 'form', 'mensagens'));
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'nome'        => 'required|string|max:255',
            'numero'      => 'required|string|max:50',
            'edificacao'  => 'required|string|max:255',
            'cidade'      => 'required|string|max:255',
            'mensagem_id' => 'required|exists:mensagens,id',
            'procurava_oque' => 'nullable|string|max:255',
            'retorno'        => 'nullable|string|max:255',
            'temperatura'    => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        Cliente::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Cliente criado com sucesso!',
        ]);
    }

    public function edit(Cliente $cliente)
    {
        return response()->json([
            'success' => true,
            'cliente' => $cliente
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'nome'        => 'required|string|max:255',
            'numero'      => 'required|string|max:50',
            'edificacao'  => 'required|string|max:255',
            'cidade'      => 'required|string|max:255',
            'mensagem_id' => 'required|exists:mensagens,id',
            'procurava_oque' => 'nullable|string|max:255',
            'retorno'        => 'nullable|string|max:255',
            'temperatura'    => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $updated = $cliente->update($data);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Cliente atualizado com sucesso!']);
        }

        return response()->json(['success' => false, 'message' => 'Erro ao atualizar'], 500);
    }

    public function destroy(Request $request, $id)
    {
        $deleted = Cliente::destroy($id);

        return response()->json([
            'success' => (bool)$deleted,
            'message' => $deleted ? 'Cliente excluído com sucesso!' : 'Cliente não encontrado'
        ]);
    }
}
