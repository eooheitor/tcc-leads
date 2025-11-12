<?php
// app/View/Forms/UserForm.php

namespace App\View\Forms;

use App\Models\User;
use App\View\Base\FormBuilder;

class UserForm extends FormBuilder
{
    public function __construct($users = null,)
    {
        $this->setTitle('Novo usuário');
        $this->setRouteForm(route('users.store'));
        $this->setMethod('POST');

        if ($users) {
            $this->setTitle('Editar Usuário');
            $this->setRouteForm(route('users.update', $users->id));
            $this->setMethod('PUT');
        }

        $this->build($users);
    }

    protected function build($users)
    {
        $this->text('name', 'Nome', $users->name ?? '');

        $this->email('email', 'E-mail', $users->email ?? '');

        $this->select(
            'role',
            'Perfil de acesso',
            [
                User::ROLE_ADMIN => 'Administrador',
                User::ROLE_USER  => 'Usuário',
            ],
            $users->role ?? User::ROLE_USER
        );

        $this->password(
            'password',
            $users ? 'Nova senha' : 'Senha'
        );

        $this->password('password_confirmation', 'Confirmar senha');

        $this->submit($users ? 'Atualizar usuário' : 'Criar usuário');

        return $this;
    }
}
