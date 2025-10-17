<?php

namespace App\View\Grids;

use App\View\Base\GridBuilder;

class AnuncioGrid extends GridBuilder
{
    public function __construct($anuncios)
    {
        parent::__construct($anuncios);

        $this->setTitle('Anúncios');
        // $this->setFormView(\App\View\Forms\MensagemForm::class);
        $this->setRouteDelete('');
        $this->setRouteCreate('');
        $this->setRouteEdit('');
        $this->setModelName('anuncios');

        $this->getColumnsView();
    }

    protected function getColumnsView()
    {
        // $this->column('id', 'ID');
        $this->column('name', 'Nome');
        $this->column('status', 'Status');
        $this->column('effective_status', 'Status efetivo');
        // $this->column('adset_id', 'AD set id', fn($row) => $row->adset_id ?? '-');
        $this->column('campaign_id', 'Campaign id');
        // $this->column('creative', 'Creative');
        $this->imageColumn('creative.image_url', 'Imagem do Anúncio');
    }
}
