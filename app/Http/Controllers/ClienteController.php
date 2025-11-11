<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Mensagem;
use App\Services\LeadService;
use App\View\Forms\ClienteForm;
use App\View\Grids\ClienteGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    protected WhatsappService $whatsappService;
    protected LeadService $leadService;

    public function __construct(WhatsappService $whatsappService, LeadService $leadService)
    {
        $this->whatsappService = $whatsappService;
        $this->leadService = $leadService;
    }

    public function index()
    {
        $importCount = $this->importRecentLeadsFromMeta(7);

        $clientes  = Cliente::with('mensagem')->latest()->paginate(10);
        $mensagens = Mensagem::orderBy('id')->get();
        $grid      = new ClienteGrid($clientes, $mensagens);
        $form      = new ClienteForm();

        return view('clientes.index', [
            'grid'         => $grid,
            'clientes'     => $clientes,
            'form'         => $form,
            'mensagens'    => $mensagens,
            'importCount'  => $importCount, // <--- para mostrar na tela
        ]);
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

    // public function whatsapp(Cliente $cliente)
    // {
    //     try {
    //         // 1) Monta o texto a partir do template (já está ótimo)
    //         $mensagem = $this->parseTemplate($cliente->mensagem->mensagem, $cliente);

    //         // 2) Normaliza telefone para formato E.164 (ex.: 5547999999999)
    //         $to = $this->normalizePhoneToE164($cliente->numero);
    //         if (!$to) {
    //             throw new \RuntimeException('Número de telefone inválido para envio pelo WhatsApp.');
    //         }

    //         // 3) Chama a Cloud API
    //         $result = $this->whatsappService->sendText($to, $mensagem);

    //         // inspecionar $result (id da mensagem, etc.)

    //         if (request()->ajax()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Mensagem enviada com sucesso pelo WhatsApp.',
    //                 'result'  => $result,
    //             ]);
    //         }

    //         return redirect()
    //             ->back()
    //             ->with('success', 'Mensagem enviada com sucesso pelo WhatsApp.');
    //     } catch (\Throwable $e) {
    //         Log::error('clientes.whatsapp error', [
    //             'cliente_id' => $cliente->id ?? null,
    //             'msg'        => $e->getMessage(),
    //         ]);

    //         $msg = 'Erro ao enviar mensagem pelo WhatsApp: ' . $e->getMessage();

    //         if (request()->ajax()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => $msg,
    //             ], 500);
    //         }

    //         return redirect()
    //             ->back()
    //             ->with('error', $msg);
    //     }
    // }

    public function whatsapp(Cliente $cliente)
    {
        $mensagem = $this->parseTemplate($cliente->mensagem->mensagem, $cliente);
        $url = 'https://wa.me/' . $cliente->numero . '?text=' . urlencode($mensagem);

        return redirect()->away($url);
    }

    function parseTemplate($template, Cliente $cliente)
    {
        $user = Auth::user();

        $vars = [
            'NOME'             => $cliente->nome,
            'PROCURAVA'   => $cliente->procurava_oque ?? '',
            'CIDADE'           => $cliente->cidade,
            'NOME_DO_CORRETOR' => $user->name,
        ];

        $texto = preg_replace_callback('/{{(.*?)}}/', function ($matches) use ($vars) {
            $key = trim($matches[1]);
            return $vars[$key] ?? $matches[0];
        }, $template);

        $texto = str_replace(['<div>', '</div>'], '', $texto);
        $texto = str_replace(['<br>', '<br/>', '<br />'], "\n", $texto);

        return html_entity_decode($texto, ENT_QUOTES, 'UTF-8');
    }

    protected function normalizePhoneToE164(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '') {
            return null;
        }

        if (!str_starts_with($digits, '55')) {
            $digits = '55' . $digits;
        }

        if (strlen($digits) < 12 || strlen($digits) > 13) {
            return null;
        }

        return $digits;
    }

    /**
     * Importa leads (mock) da Meta e grava na tabela clientes.
     *
     * @return int Quantidade de novos leads inseridos
     */
    protected function importRecentLeadsFromMeta(int $days = 7): int
    {
        try {
            $leads = $this->leadService->fetchRecentLeadsMock($days);
        } catch (\Throwable $e) {
            Log::error('clientes.importMeta error', [
                'msg' => $e->getMessage(),
            ]);
            return 0;
        }

        $imported = 0;

        foreach ($leads as $lead) {
            $fieldData = $lead['field_data'] ?? [];
            if (empty($fieldData)) {
                continue;
            }

            // 1) Mapeia o field_data para um array compatível com Cliente::$fillable
            $payload = $this->mapLeadFieldDataToCliente($fieldData);
            if (!$payload) {
                continue;
            }

            // 2) Normaliza telefone (mesma regra do store)
            $numeroNormalizado = $this->normalizePhone($payload['numero'] ?? null);
            if (!$numeroNormalizado) {
                continue;
            }

            // 3) Evita duplicar clientes com o mesmo número
            $jaExiste = Cliente::where('numero', $numeroNormalizado)->exists();
            if ($jaExiste) {
                continue;
            }

            $payload['numero'] = $numeroNormalizado;

            // 4) Mensagem padrão se não tiver
            if (empty($payload['mensagem_id'])) {
                $payload['mensagem_id'] = Mensagem::query()->value('id');
            }

            // 5) Validação básica reutilizando as mesmas regras do create
            //    (sem explodir a request original da tela)
            $validator = Validator::make($payload, [
                'nome'           => ['required', 'string', 'max:255'],
                'numero'         => ['required', 'string', 'regex:/^\+?[0-9\s\-\(\)]{10,20}$/'],
                'mensagem_id'    => ['required', 'integer', 'exists:mensagens,id'],
                'edificacao'     => ['nullable', 'string', 'max:255'],
                'cidade'         => ['nullable', 'string', 'max:255'],
                'procurava_oque' => ['nullable', 'string', 'max:255'],
                'retorno'        => ['nullable', 'string', 'max:255'],
                'temperatura'    => ['nullable', 'string', 'max:50'],
            ]);

            if ($validator->fails()) {
                Log::warning('clientes.importMeta validation fail', [
                    'errors' => $validator->errors()->toArray(),
                    'payload' => $payload,
                ]);
                continue;
            }

            Cliente::create($validator->validated());
            $imported++;
        }

        return $imported;
    }

    /**
     * Converte o field_data do lead da Meta em um array compatível com Cliente::$fillable.
     *
     * Estrutura esperada:
     * [
     *   ['name' => 'full_name',    'values' => ['Fulano da Silva']],
     *   ['name' => 'phone_number', 'values' => ['(47) 99999-0000']],
     *   ...
     * ]
     */
    protected function mapLeadFieldDataToCliente(array $fieldData): ?array
    {
        // Achata o field_data em algo tipo ['full_name' => 'Fulano', 'phone_number' => '...']
        $flat = [];
        foreach ($fieldData as $field) {
            $name   = $field['name']   ?? null;
            $values = $field['values'] ?? [];

            if (!$name || empty($values)) {
                continue;
            }

            $flat[$name] = $values[0];
        }

        // Nome (full_name ou first_name + last_name)
        $nome = $flat['full_name']
            ?? trim(($flat['first_name'] ?? '') . ' ' . ($flat['last_name'] ?? ''));

        // Telefone
        $telefone = $flat['phone_number']
            ?? $flat['phone']
            ?? $flat['telefone']
            ?? null;

        if (!$telefone && !$nome) {
            return null;
        }

        $cidade = $flat['city']    ?? $flat['cidade']    ?? null;
        $proc   = $flat['procurava_oque'] ?? $flat['interesse'] ?? null;
        $edif   = $flat['edificacao'] ?? null;

        return [
            'nome'           => $nome ?: 'Lead Facebook',
            'numero'         => $telefone ?: '',
            'edificacao'     => $edif,
            'cidade'         => $cidade,
            'procurava_oque' => $proc,
            'retorno'        => 'Lead importado da Meta',
            'temperatura'    => 'Novo',
            // 'mensagem_id' será preenchido no importRecentLeadsFromMeta se estiver vazio
        ];
    }
}
