<?php

namespace App\View\Grids;

use App\View\Base\GridBuilder;

class AnuncioGrid extends GridBuilder
{
    public function __construct($anuncios)
    {
        parent::__construct($anuncios);

        $this->setTitle('Anúncios');
        $this->setFormView(\App\View\Forms\AnuncioForm::class);
        $this->setRouteDelete('');
        $this->setRouteCreate('');
        $this->setRouteEdit('');
        $this->setModelName('anuncios');

        $this->getColumnsView();
    }

    protected function getColumnsView()
    {
        $this->column('name', 'Nome');
        $this->column('status', 'Status');
        $this->column('effective_status', 'Status efetivo');
        $this->column('campaign_id', 'Campaign id');
        $this->imageColumn('creative.image_url', 'Imagem do Anúncio');
    }
}
