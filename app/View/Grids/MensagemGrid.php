<?php

namespace App\View\Grids;

use App\View\Base\GridBuilder;

class MensagemGrid extends GridBuilder
{
    public function __construct($mensagens)
    {
        parent::__construct($mensagens);

        $this->setTitle('Mensagens');
        $this->setFormView(\App\View\Forms\MensagemForm::class);
        $this->setRouteDelete('mensagens.destroy');
        $this->setRouteCreate('mensagens.store');
        $this->setRouteEdit('mensagens.edit');
        $this->setModelName('mensagens');

        $this->getColumnsView();
    }

    protected function getColumnsView()
    {
        $this->column('id', 'ID');
        $this->column('titulo', 'Titulo');
        $this->column('mensagem', 'Mensagem');
    }
}
