<?php

namespace App\View\Forms;

use App\View\Base\FormBuilder;

class AnuncioForm extends FormBuilder
{
    public function __construct($anuncio = null)
    {
        $this->setTitle('Novo Anúncio');
        $this->setRouteForm(route('anuncios.store'));
        $this->setMethod('POST');

        if ($anuncio) {
            $this->setTitle('Editar Anúncio');
            $this->setRouteForm(route('anuncios.update', $anuncio->id));
            $this->setMethod('PUT');
        }

        $this->build($anuncio);
    }

    public function build($anuncio = null): self
    {
        $this->text('name', 'Nome', $anuncio->name ?? '');
        $this->select('status', 'Status', [
            'ACTIVE' => 'Ativo',
            'PAUSED' => 'Pausado',
            'DELETED' => 'Deletado',
        ], $anuncio->status ?? 'ACTIVE');

        $this->select('effective_status', 'Status Efetivo', [
            'ACTIVE' => 'Ativo',
            'PAUSED' => 'Pausado',
            'IN_REVIEW' => 'Em Revisão',
            'DISAPPROVED' => 'Reprovado',
        ], $anuncio->effective_status ?? 'ACTIVE');

        // $this->text('campaign_id', 'Campaign ID', $anuncio->campaign_id ?? '');
        $this->file('creative.image_url', 'Imagem do Anúncio');

        $this->submit($anuncio ? 'Atualizar' : 'Salvar');

        return $this;
    }
}
