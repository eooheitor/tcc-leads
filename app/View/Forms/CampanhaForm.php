<?php

namespace App\View\Forms;

use App\View\Base\FormBuilder;

class CampanhaForm extends FormBuilder
{
    public function __construct($campanha = null)
    {
        $this->setTitle('Nova Campanha');
        $this->setRouteForm(route('campanhas.store'));
        $this->setMethod('POST');

        if ($campanha) {
            $this->setTitle('Editar Campanha');
            $this->setRouteForm(route('campanhas.update', $campanha->id));
            $this->setMethod('PUT');
        }

        $this->build($campanha);
    }

    public function build($campanha = null): self
    {
        $this->text('name', 'Nome', $campanha->name ?? '');
        
        $this->select('status', 'Status', [
            'ACTIVE' => 'Ativo',
            'PAUSED' => 'Pausado',
            'DELETED' => 'Deletado',
        ], $campanha->status ?? 'ACTIVE');

        $this->select('effective_status', 'Status Efetivo', [
            'ACTIVE' => 'Ativo',
            'PAUSED' => 'Pausado',
            'IN_REVIEW' => 'Em Revisão',
            'DISAPPROVED' => 'Reprovado',
        ], $campanha->effective_status ?? 'ACTIVE');

        $this->text('daily_budget', 'Orçamento Diário', $campanha->daily_budget ?? 0);

        $this->submit($campanha ? 'Atualizar' : 'Salvar');

        return $this;
    }
}
