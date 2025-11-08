<?php

namespace App\View\Grids;

use App\Models\Campanha;
use App\View\Base\GridBuilder;

class CampanhaGrid extends GridBuilder
{
    public function __construct($campanhas)
    {
        parent::__construct($campanhas);

        $this->setTitle('Campanhas');
        $this->setFormView(\App\View\Forms\CampanhaForm::class);
        $this->setRouteName('campanhas');
        $this->setRouteCreate('campanhas.store');
        $this->setRouteEdit('campanhas.update');
        $this->setRouteDelete('campanhas.destroy');
        $this->setModelName('campanhas');

        $this->getColumnsView();
    }

    protected function getColumnsView()
    {
        $this->column('name', 'Nome da Campanha');
        $this->column('buying_type', 'Tipo de Compra', function ($row) {
            return Campanha::getTipoCompra()[$row->buying_type] ?? '-';
        });
        $this->column('objective', 'Objetivo', function ($row) {
            return Campanha::getObjetivos()[$row->objective] ?? '-';
        });
        $this->column('special_ad_categories', 'Categoria de Anúncios', function ($row) {
            $val = is_array($row->special_ad_categories)
                ? ($row->special_ad_categories[0] ?? 'NONE')
                : ($row->special_ad_categories ?? 'NONE');

            return Campanha::getCategoriasAnuncios()[$val] ?? '-';
        });
        $this->column('daily_budget', 'Orçamento Diário (R$)', function ($row) {
            return isset($row->daily_budget)
                ? number_format($row->daily_budget / 100, 2, ',', '.')
                : '-';
        });
        $this->column('status', 'Status', fn($row) => $row->status ?? '-');
    }
}
