<?php

namespace App\View\Forms;

use App\View\Base\FormBuilder;

class ClienteForm extends FormBuilder
{
    public function __construct($cliente = null, $mensagens = null)
    {
        $isEdit = (bool) data_get($cliente, 'id');

        $this->setEditMode($isEdit)->disabledOnEdit([]);

        $this->setTitle($isEdit ? 'Editar lead' : 'Novo lead');
        $this->setRouteForm($isEdit ? url('clientes/' . data_get($cliente, 'id')) : route('clientes.store'));
        $this->setMethod($isEdit ? 'PUT' : 'POST');

        $this->withData(['mensagens' => $mensagens ?? collect()]);
        $this->build($cliente);
    }

    public function build($cliente = null): self
    {
        $this->getFieldsForm($cliente);
        return $this;
    }

    protected function getFieldsForm($cliente)
    {
        $mensagens = $this->getData('mensagens');

        $this->text('nome', 'Nome', $cliente->nome ?? '');
        $this->phone('numero', 'Telefone', $cliente->numero ?? '', '(00) 00000-0000');
        $this->select(
            'mensagem_id',
            'Mensagem',
            $mensagens->pluck('titulo', 'id')->toArray(),
            $cliente->mensagem_id ?? ''
        );
        $this->text('edificacao', 'Edificação', $cliente->edificacao ?? '');
        $this->text('cidade', 'Cidade', $cliente->cidade ?? '');
        $this->date('data_contato', 'Data do Contato', $cliente->data_contato ?? '');
        $this->text('procurava_oque', 'Procurava oque', $cliente->procurava_oque ?? '');
        $this->text('retorno', 'Retorno', $cliente->retorno ?? '');
        $this->text('temperatura', 'Temperatura', $cliente->temperatura ?? '');
        $this->submit($cliente ? 'Atualizar' : 'Salvar');
    }
}
