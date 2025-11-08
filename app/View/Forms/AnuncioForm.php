<?php

namespace App\View\Forms;

use App\View\Base\FormBuilder;

class AnuncioForm extends FormBuilder
{
    private array $adsetOptions;

    public function __construct($anuncio = null, array $adsetOptions = [])
    {
        $this->adsetOptions = $adsetOptions;
        $routeForm = $anuncio ? route('anuncios.update', $anuncio['id'] ?? $anuncio->id ?? null)
                : route('anuncios.store');
        $this->setTitle($anuncio ? 'Editar Anúncio' : 'Novo Anúncio');
        $this->setRouteForm($routeForm);
        $this->setMethod($anuncio ? 'PUT' : 'POST');

        $this->build($anuncio);
    }

    public function build($anuncio = null): self
    {
        $this->text('name', 'Nome do Anúncio', $anuncio['name'] ?? $anuncio->name ?? '', 'Ex.: Anúncio Residencial Zandonai');

        // Selecionar Ad Set
        $this->select('adset_id', 'Conjunto de Anúncios (Ad Set)', $this->adsetOptions, $anuncio['adset_id'] ?? $anuncio->adset_id ?? '');

        // Upload de imagem (criativo)
        $this->setMultipart(true);
        $this->file('creative_file', 'Imagem do Anúncio (Criativo)', 'image/*');

        // Status inicial
        $this->select('status', 'Status do Anúncio', [
            'ACTIVE'   => 'Ativo',
            'PAUSED'   => 'Pausado',
            'ARCHIVED' => 'Arquivado',
            'DELETED'  => 'Deletado',
        ], $anuncio['status'] ?? $anuncio->status ?? 'PAUSED');

        // Tipo de destino
        $this->select('destination_type', 'Tipo de Destino', [
            'WEBSITE'   => 'Website',
            'APP'       => 'Aplicativo',
            'MESSENGER' => 'Messenger',
        ], $anuncio['destination_type'] ?? $anuncio->destination_type ?? 'WEBSITE');

        $this->submit($anuncio ? 'Atualizar Anúncio' : 'Criar Anúncio');

        return $this;
    }
}
