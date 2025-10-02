<?php

namespace App\View\Grids;

use App\View\Base\GridBuilder;

class ClienteGrid extends GridBuilder
{
    public function __construct($clientes, $mensagens)
    {
        parent::__construct($clientes);

        $this->setTitle('Clientes');
        $this->setFormView(\App\View\Forms\ClienteForm::class);
        $this->setRouteDelete('clientes.destroy');
        $this->setRouteCreate('clientes.store');
        $this->setRouteEdit('clientes.edit');
        $this->setModelName('cliente');
        $this->setFormData([
            'mensagens' => $mensagens,
        ]);

        $this->getColumnsView();
    }

    protected function getColumnsView()
    {
        $this->column('id', 'ID');
        $this->column('nome', 'Nome');
        $this->column('numero', 'Número');
        $this->column('edificacao', 'Edificação');
        $this->column('cidade', 'Cidade');
        $this->column('procurava_oque', 'Procurava o quê');
        $this->column('retorno', 'Retorno');
        $this->column('temperatura', 'Temperatura');

        $this->column('mensagem_id', 'Mensagem', function ($cliente) {
            return $cliente->mensagem->titulo ?? '-';
        });
    }
}
