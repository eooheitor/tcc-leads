<?php

namespace App\View\Grids;

use App\Models\User;
use App\View\Base\GridBuilder;
use App\View\Forms\UserForm;

class UserGrid extends GridBuilder
{
    public function __construct($rows)
    {
        parent::__construct($rows);
        $this->setTitle('Usuários');
        $this->setFormView(UserForm::class);
        $this->setModelName('users');
        $this->setRouteName('users');
        $this->setRouteCreate('users.store');
        $this->setRouteEdit('users.update');
        $this->setRouteDelete('users.destroy');

        $this->getColumnsView();
    }

    protected function formatRoles($role)
    {
        if ($role === User::ROLE_ADMIN) {
            return 'Administrador';
        } elseif ($role === User::ROLE_USER) {
            return 'Usuário';
        }
    }

    protected function getColumnsView()
    {
        $this->column('id', 'ID', fn($row) => $row->id);

        $this->column('name', 'Nome', fn($row) => e($row->name));

        $this->column('email', 'E-mail', fn($row) => e($row->email));

        $this->column('role', 'Perfil', function ($row) {
            return $row->role === User::ROLE_ADMIN
                ? 'Administrador'
                : 'Usuário';
        });

        $this->column('created_at', 'Criado em', function ($row) {
            return optional($row->created_at)->format('d/m/Y H:i');
        });
    }
}
