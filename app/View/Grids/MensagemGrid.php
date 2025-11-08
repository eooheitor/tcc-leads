<?php

namespace App\View\Grids;

use App\View\Base\GridBuilder;

class MensagemGrid extends GridBuilder
{
    public function __construct($rows)
    {
        parent::__construct($rows);

        $this->setTitle('Mensagens');
        $this->setFormView(\App\View\Forms\MensagemForm::class);
        $this->setModelName('mensagens');
        $this->setRouteName('mensagens');  
        $this->setRouteCreate('mensagens.store');
        $this->setRouteEdit('mensagens.update');
        $this->setRouteDelete('mensagens.destroy');

        $this->getColumnsView();
    }

    protected function getColumnsView()
    {
        $this->column('id', 'ID');
        $this->column('titulo', 'Titulo');
        $this->columnArea('mensagem', 'Mensagem');
    }
}
