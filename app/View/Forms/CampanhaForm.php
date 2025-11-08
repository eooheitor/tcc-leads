<?php

namespace App\View\Forms;

use App\Models\Campanha;
use App\View\Base\FormBuilder;

class CampanhaForm extends FormBuilder
{
    public function __construct($campanha = null)
    {
        $isEdit = (bool) data_get($campanha, 'id');
        $routeForm = $isEdit ? url('campanhas/' . data_get($campanha, 'id')) : route('campanhas.store');
        $this->setTitle($isEdit ? 'Editar Campanha' : 'Nova Campanha');
        $this->setRouteForm($routeForm);
        $this->setMethod($isEdit ? 'PUT' : 'POST');

        $this->setEditMode($isEdit)->disabledOnEdit($this->disabledEdit());

        $this->build($campanha);
    }

    public function build($campanha = null): self
    {
        $this->getFieldsForm($campanha);
        return $this;
    }

    protected function getFieldsForm($campanha)
    {
        $val = fn($key, $default = '') => data_get($campanha, $key, $default);

        $this->text('name', 'Nome da Campanha', $val('name', ''));

        $this->select(
            'buying_type',
            'Tipo de compra',
            Campanha::getTipoCompra(),
            $val('buying_type', 'AUCTION')
        );

        $this->select(
            'objective',
            'Objetivo da Campanha',
            Campanha::getObjetivos(),
            $val('objective', 'OUTCOME_LEADS')
        );

        $this->money('daily_budget', 'Orçamento Diário (R$)', $val('daily_budget', null));

        $selectedCat = $val('special_ad_categories_select') ?? ($val('special_ad_categories.0') ?? 'NONE');

        $this->select(
            'special_ad_categories[]',
            'Categoria de anúncios',
            Campanha::getCategoriasAnuncios(),
            $selectedCat
        );

        $this->submit($val('id') ? 'Atualizar Campanha' : 'Criar Campanha');
    }

    protected function disabledEdit(): array
    {
        return [
            'buying_type',
            'special_ad_categories[]',
            'objective'
        ];
    }
}
