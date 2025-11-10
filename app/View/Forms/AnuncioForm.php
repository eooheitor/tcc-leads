<?php

namespace App\View\Forms;

use App\View\Base\FormBuilder;

class AnuncioForm extends FormBuilder
{
    public function __construct($anuncio = null)
    {
        $this->setTitle($anuncio ? 'Editar Anúncio' : 'Novo Anúncio');

        $this->setRouteForm(
            $anuncio
                ? route('anuncios.update', data_get($anuncio, 'id'))
                : route('anuncios.store')
        );

        $this->setMethod($anuncio ? 'PUT' : 'POST');

        // vamos trabalhar com upload de imagem do criativo
        $this->setMultipart(true);

        $this->build($anuncio);
    }

    public function build($anuncio = null): self
    {
        $val = fn(string $key, $default = '') => data_get($anuncio, $key, $default);

        // ==== GRID 2 COLUNAS (campos "simples") ====
        $this->raw('<div class="grid grid-cols-1 md:grid-cols-2 gap-4">');

        // Linha 1: Nome / Status
        $this->text(
            'ad_name',
            'Nome do Anúncio',
            $val('name', ''),
        );

        $this->select(
            'status',
            'Status',
            [
                'ACTIVE' => 'Ativo',
                'PAUSED' => 'Pausado',
            ],
            $val('status', 'PAUSED')
        );

        // Linha 2: Imagem / URL
        // (ajusta pro método que você realmente tem: file() ou fileMultiple())
        $this->fileMultiple(
            'images[]',
            'Imagem do anúncio (criativo)',
            'image/*'
        );

        $this->text(
            'link_url',
            'URL de destino',
            $val('link_url', ''),
            'https://'
        );

        $this->raw('</div>'); // fecha grid 2 colunas

        // ==== CAMPOS FULL-WIDTH ====
        // Texto principal (Trix)
        $this->textarea(
            'primary_text',
            'Texto principal',
            $val('primary_text', '')
        );

        // Headline
        $this->text(
            'headline',
            'Título (headline)',
            $val('headline', '')
        );

        // Descrição
        $this->text(
            'description',
            'Descrição',
            $val('description', '')
        );

        // CTA
        $this->select(
            'call_to_action',
            'Chamada para ação',
            [
                'LEARN_MORE' => 'Saiba mais',
                'SIGN_UP'    => 'Cadastrar',
                'APPLY_NOW'  => 'Aplicar agora',
                'CONTACT_US' => 'Fale conosco',
                'DOWNLOAD'   => 'Baixar',
            ],
            $val('call_to_action', 'LEARN_MORE')
        );

        // Botão
        // $this->submit($anuncio ? 'Atualizar anúncio' : 'Criar anúncio');

        return $this;
    }
}
