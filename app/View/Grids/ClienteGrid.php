<?php

namespace App\View\Grids;

use App\View\Base\GridBuilder;

class ClienteGrid extends GridBuilder
{
    public function __construct($clientes)
    {
        parent::__construct($clientes);

        $this->setTitle('Leads');
        $this->setFormView(\App\View\Forms\ClienteForm::class);
        $this->setModelName('cliente'); 
        $this->setRouteName('clientes');    
        $this->setRouteCreate('clientes.store');
        $this->setRouteEdit('clientes.update');
        $this->setRouteDelete('clientes.destroy');

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
