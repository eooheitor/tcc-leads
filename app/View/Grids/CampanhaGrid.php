<?php

namespace App\View\Grids;

use App\View\Base\GridBuilder;

class CampanhaGrid extends GridBuilder
{
    public function __construct($campanhas)
    {
        parent::__construct($campanhas);

        $this->setTitle('Campanhas');
        $this->setFormView(\App\View\Forms\CampanhaForm::class);
        $this->setRouteDelete('');
        $this->setRouteCreate('');
        $this->setRouteEdit('');
        $this->setModelName('campanhas');

        $this->getColumnsView();
    }

    protected function getColumnsView()
    {
        $this->column('id', 'ID');
        $this->column('name', 'Nome');
        $this->column('status', 'Status');
        $this->column('effective_status', 'Status efetivo');
        $this->column('daily_budget', 'Orçamento diário', fn($row) => $row->daily_budget ?? '-');
    }
}
