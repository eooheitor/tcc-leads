<?php

// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\View\Forms\UserForm;
use App\View\Grids\UserGrid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'desc')->paginate(10);
        $grid  = new UserGrid($users);
        $form  = new UserForm();

        return view('users.index', compact('grid', 'form', 'users'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCreate($request);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Usuário criado com sucesso!',
            'user'    => $user,
        ], 201);
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $data = $this->validateUpdate($request, $user);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso!',
            'user'    => $user->fresh(),
        ]);
    }

    public function destroy(string $id)
    {
        // evita apagar a si mesmo
        if ((int)$id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode excluir o próprio usuário.',
            ], 400);
        }

        $deleted = User::destroy($id);

        return response()->json([
            'success' => (bool) $deleted,
            'message' => $deleted ? 'Usuário excluído com sucesso!' : 'Usuário não encontrado.',
        ], $deleted ? 200 : 404);
    }

    // ====== FORM MODAL (igual padrão clientes / adsets) ======

    public function formCreate()
    {
        $form = new UserForm();

        return response()->json([
            'success'     => true,
            'title'       => $form->getTitle(),
            'method'      => $form->getMethod(),
            'action'      => $form->getRouteForm(),
            'multipart'   => false,
            'fields_html' => $form->render(),
        ]);
    }

    public function formEdit(string $id)
    {
        $user = User::findOrFail($id);
        $form = new UserForm($user);

        return response()->json([
            'success'     => true,
            'title'       => $form->getTitle(),
            'method'      => $form->getMethod(),
            'action'      => $form->getRouteForm(),
            'multipart'   => false,
            'fields_html' => $form->render(),
        ]);
    }

    // ====== validações ======

    protected function validateCreate(Request $request): array
    {
        return $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'  => ['required', 'in:1,2'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    protected function validateUpdate(Request $request, User $user): array
    {
        return $request->validate([
            'name'  => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role'  => ['sometimes', 'required', 'in:1,2'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);
    }
}
