<?php

namespace App\View\Forms;

use App\View\Base\FormBuilder;

class MensagemForm extends FormBuilder
{
    public function __construct($mensagem = null,)
    {   
        $this->setTitle('Nova mensagem');
        $this->setRouteForm(route('mensagens.store'));
        $this->setMethod('POST');

        if ($mensagem) {
            $this->setTitle('Editar Mensagens');
            $this->setRouteForm(route('mensagens.update', $mensagem->id));
            $this->setMethod('PUT');
        }

        $this->build($mensagem);
    }

    public function build($mensagem = null): self
    {
        $this->text('titulo', 'Titulo', $mensagem->titulo ?? '');
        $this->textarea('mensagem', 'Mensagem', $mensagem->mensagem ?? '');
        $this->submit($mensagem ? 'Atualizar' : 'Salvar');

        return $this;
    }
}
