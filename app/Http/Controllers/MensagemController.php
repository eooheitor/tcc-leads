<?php

namespace App\Http\Controllers;

use App\Models\Mensagem;
use App\View\Grids\MensagemGrid;
use Illuminate\Http\Request;

class MensagemController extends Controller
{
    public function index()
    {
        $mensagens = Mensagem::orderBy('id')->paginate(10);
        $grid = new MensagemGrid($mensagens);
        $form = new \App\View\Forms\MensagemForm($mensagem ?? null);
        return view('mensagens.index', compact('grid', 'mensagens', 'form'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'titulo'   => 'required|string|max:255',
                'mensagem' => 'required|string',
            ]);

            $dados = $request->only('titulo', 'mensagem');

            $mensagem = Mensagem::create($dados);

            if ($request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Mensagem criada com sucesso!',
                    'mensagem' => $mensagem
                ]);
            }

            return redirect()->route('mensagens.index')->with('success', 'Mensagem criada com sucesso!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Ocorreu um erro ao salvar. {$e->getMessage()}"
                ]);
            }

            return redirect()->back()->with('error', "Ocorreu um erro ao salvar. {$e->getMessage()}");
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $mensagem = Mensagem::find($id);
            if (!$mensagem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mensagem não encontrada.'
                ]);
            }

            $dados = $request->only('titulo', 'mensagem');
            $mensagem->update($dados);

            if ($request->ajax()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Mensagem atualizada com sucesso!',
                    'mensagem' => $mensagem
                ]);
            }

            return redirect()->route('mensagens.index')->with('success', 'Mensagem atualizada com sucesso!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => "Ocorreu um erro ao atualizar. {$e->getMessage()}"
                ]);
            }

            return redirect()->back()->with('error', "Ocorreu um erro ao atualizar. {$e->getMessage()}");
        }
    }

    public function edit($id)
    {
        $mensagem = \App\Models\Mensagem::find($id);
        if (!$mensagem) {
            return response()->json([
                'success' => false,
                'message' => 'Mensagem não encontrada.'
            ]);
        }
        return response()->json([
            'success'  => true,
            'mensagem' => $mensagem
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $mensagem = Mensagem::findOrFail($id);
        $mensagem->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Mensagem deletada com sucesso'
            ]);
        }

        return redirect()->route('mensagens.index')->with('success', 'Mensagem deletada com sucesso');
    }
}
