<?php

namespace App\Http\Controllers;

use App\Models\Mensagem;
use App\View\Grids\MensagemGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MensagemController extends Controller
{
    public function index()
    {
        $mensagens = Mensagem::orderBy('id')->paginate(10);
        $grid = new MensagemGrid($mensagens);
        $form = new \App\View\Forms\MensagemForm();
        return view('mensagens.index', compact('grid', 'mensagens', 'form'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCreate($request);
        $mensagem = Mensagem::create($data);

        return response()->json([
            'success'  => true,
            'message'  => 'Mensagem criada com sucesso!',
            'mensagem' => $mensagem,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $mensagem = Mensagem::findOrFail($id);
        $data = $this->validateUpdate($request);
        $mensagem->update($data);

        return response()->json([
            'success'  => true,
            'message'  => 'Mensagem atualizada com sucesso!',
            'mensagem' => $mensagem,
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $mensagem = Mensagem::findOrFail($id);
        $mensagem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mensagem excluída com sucesso!',
        ]);
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

    private function validateCreate(Request $request): array
    {
        return $request->validate([
            'titulo'    => ['required', 'string', 'max:255'],
            'mensagem'  => ['required', 'string'],
        ]);
    }

    private function validateUpdate(Request $request): array
    {
        return $request->validate([
            'titulo'    => ['required', 'string', 'max:255'],
            'mensagem'  => ['required', 'string'],
        ]);
    }

    public function formCreate()
    {
        try {
            $form = new \App\View\Forms\MensagemForm(null);
            return response()->json([
                'success'      => true,
                'title'        => $form->getTitle(),
                'method'       => $form->getMethod(),
                'action'       => $form->getRouteForm(),
                'multipart'    => false,
                'fields_html'  => $form->render(),
            ]);
        } catch (\Throwable $e) {
            Log::error('mensagens.formCreate error', ['ex' => $e]);
            return response()->json(['success' => false, 'message' => 'Erro ao carregar formulário.'], 500);
        }
    }

    public function formEdit(string $id)
    {
        try {
            $mensagem = Mensagem::findOrFail($id);
            $form = new \App\View\Forms\MensagemForm($mensagem);

            return response()->json([
                'success'      => true,
                'title'        => $form->getTitle(),
                'method'       => 'PUT',
                'action'       => url('mensagens/' . $mensagem->id),
                'multipart'    => false,
                'fields_html'  => $form->render(),
            ]);
        } catch (\Throwable $e) {
            Log::error('mensagens.formEdit error', ['ex' => $e]);
            return response()->json(['success' => false, 'message' => 'Erro ao carregar formulário: ' . $e->getMessage()], 500);
        }
    }
}
