<?php

namespace App\View\Forms;

use App\View\Base\FormBuilder;

class ClienteForm extends FormBuilder
{
    public function __construct($cliente = null, $mensagens = null)
    {   
        $this->setTitle('Novo cliente');
        $this->setRouteForm(route('clientes.store'));
        $this->setMethod('POST');

        if ($cliente) {
            $this->setTitle('Editar Cliente');
            $this->setRouteForm(route('clientes.update', $cliente->id));
            $this->setMethod('PUT');
        }

        $this->withData([
            'mensagens' => $mensagens ?? collect()
        ]);

        $this->build($cliente);
    }

    public function build($cliente = null): self
    {
        $mensagens = $this->getData('mensagens');

        $this->text('nome', 'Nome', $cliente->nome ?? '');
        $this->text('numero', 'Numero', $cliente->numero ?? '');
        $this->select(
            'mensagem_id',
            'Mensagem',
            $mensagens->pluck('titulo', 'id')->toArray(),
            $cliente->mensagem_id ?? ''
        );
        $this->text('edificacao', 'Edificação', $cliente->edificacao ?? '');
        $this->text('cidade', 'Cidade', $cliente->cidade ?? '');
        $this->text('procurava_oque', 'Procurava oque', $cliente->procurava_oque ?? '');
        $this->text('retorno', 'Retorno', $cliente->retorno ?? '');
        $this->text('temperatura', 'Temperatura', $cliente->temperatura ?? '');
        $this->submit($cliente ? 'Atualizar' : 'Salvar');

        return $this;
    }
}
