<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Mensagem;
use App\View\Forms\ClienteForm;
use App\View\Grids\ClienteGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes  = Cliente::with('mensagem')->latest()->paginate(10);
        $mensagens = Mensagem::orderBy('id')->get();
        $grid = new ClienteGrid($clientes, $mensagens);
        $form = new ClienteForm();

        return view('clientes.index', compact('grid', 'clientes', 'form', 'mensagens'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCreate($request);
        $data['numero'] = $this->normalizePhone($data['numero'] ?? null);

        $cliente = Cliente::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Cliente criado com sucesso!',
            'cliente' => $cliente,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $data = $this->validateUpdate($request);

        if (array_key_exists('numero', $data)) {
            $data['numero'] = $this->normalizePhone($data['numero']);
        }

        $cliente->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cliente atualizado com sucesso!',
            'cliente' => $cliente->fresh('mensagem'),
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $deleted = Cliente::destroy($id);

        return response()->json([
            'success' => (bool) $deleted,
            'message' => $deleted ? 'Cliente excluído com sucesso!' : 'Cliente não encontrado.',
        ], $deleted ? 200 : 404);
    }

    private function validateCreate(Request $request): array
    {
        return $request->validate([
            'nome'           => ['required', 'string', 'max:255'],
            'numero'         => ['required', 'string', 'regex:/^\+?[0-9\s\-\(\)]{10,20}$/'],
            'mensagem_id'    => ['required', 'integer', 'exists:mensagens,id'],
            'edificacao'     => ['required', 'string', 'max:255'],
            'cidade'         => ['required', 'string', 'max:255'],
            'data_contato'   => ['nullable', 'date_format:Y-m-d'],
            'procurava_oque' => ['nullable', 'string', 'max:255'],
            'retorno'        => ['nullable', 'string', 'max:255'],
            'temperatura'    => ['nullable', 'string', 'max:50'],
        ], [
            'numero.regex'            => 'Informe um telefone válido (pode conter +, (), espaços e traços).',
            'mensagem_id.exists'      => 'A mensagem selecionada não foi encontrada.',
            'data_contato.date_format' => 'A data deve estar no formato YYYY-MM-DD.',
        ]);
    }

    private function validateUpdate(Request $request): array
    {
        return $request->validate([
            'nome'           => ['sometimes', 'string', 'max:255'],
            'numero'         => ['sometimes', 'string', 'regex:/^\+?[0-9\s\-\(\)]{10,20}$/'],
            'mensagem_id'    => ['sometimes', 'nullable', 'integer', 'exists:mensagens,id'],
            'edificacao'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'cidade'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'data_contato'   => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'procurava_oque' => ['sometimes', 'nullable', 'string', 'max:255'],
            'retorno'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'temperatura'    => ['sometimes', 'nullable', 'string', 'max:50'],
        ], [
            'numero.regex'            => 'Informe um telefone válido (pode conter +, (), espaços e traços).',
            'mensagem_id.exists'      => 'A mensagem selecionada não foi encontrada.',
            'data_contato.date_format' => 'A data deve estar no formato YYYY-MM-DD.',
        ]);
    }

    private function normalizePhone(?string $raw): ?string
    {
        if ($raw === null) return null;
        $clean = preg_replace('/[^\d\+]/', '', $raw);
        return $clean ?: null;
    }

    public function formCreate()
    {
        try {
            $mensagens = Mensagem::orderBy('id')->get();
            $form = new ClienteForm(null, $mensagens);

            return response()->json([
                'success'      => true,
                'title'        => $form->getTitle(),
                'method'       => $form->getMethod(),
                'action'       => $form->getRouteForm(),
                'multipart'    => false,
                'fields_html'  => $form->render(),
            ]);
        } catch (\Throwable $e) {
            Log::error('clientes.formCreate error', ['ex' => $e]);
            return response()->json(['success' => false, 'message' => 'Erro ao carregar formulário.'], 500);
        }
    }

    public function formEdit(string $id)
    {
        try {
            $cliente   = Cliente::with('mensagem')->findOrFail($id);
            $mensagens = Mensagem::orderBy('id')->get();

            $form = new ClienteForm($cliente, $mensagens);

            return response()->json([
                'success'      => true,
                'title'        => $form->getTitle(),
                'method'       => 'PUT',
                'action'       => url('clientes/' . $cliente->id),
                'multipart'    => false,
                'fields_html'  => $form->render(),
            ]);
        } catch (\Throwable $e) {
            Log::error('clientes.formEdit error', ['ex' => $e]);
            return response()->json(['success' => false, 'message' => 'Erro ao carregar formulário: ' . $e->getMessage()], 500);
        }
    }
}
