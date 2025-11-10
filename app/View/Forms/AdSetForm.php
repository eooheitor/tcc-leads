<?php

namespace App\View\Forms;

use App\Models\AdSet;
use App\View\Base\FormBuilder;

class AdSetForm extends FormBuilder
{
    public function render(): string
    {
        $conjuntoHtml = parent::render();

        $hasAdset = $this->isEditMode();

        return view('components.form.adset_tabs', [
            'form'         => $this,
            'conjuntoHtml' => $conjuntoHtml,
            'hasAdset'     => $hasAdset,
        ])->render();
    }

    public function __construct($adset = null, $campanhas)
    {
        $isEdit = (bool) data_get($adset, 'id');
        $routeForm = $adset ? url('adsets/' . (string) data_get($adset, 'id', '')) : route('adsets.store');

        $this->withData(['campanhas' => $campanhas ?? collect()]);

        $this->setEditMode($isEdit)->disabledOnEdit($this->disabledEdit());
        $this->setTitle($adset ? 'Editar Conjunto de Anúncio' : 'Novo Conjunto');
        $this->setRouteForm($routeForm);
        $this->setMethod($adset ? 'PUT' : 'POST');

        $this->build($adset);
    }

    public function build($adset = null): self
    {
        $this->getFieldsForm($adset);
        return $this;
    }

    protected function getFieldsForm($adset)
    {
        $campanhas = $this->getData('campanhas');
        $val = fn(string $key, $default = '') => data_get($adset, $key, $default);
        $this->text('name', 'Nome do Conjunto', $val('name', ''));
        $this->select('campaign_id', 'Campanha', $campanhas, $val('campaign_id', ''));
        $this->select('status', 'Status', [
            'ACTIVE' => 'Ativo',
            'PAUSED' => 'Pausado',
        ], $val('status', 'PAUSED'));
        $this->select('optimization_goal', 'Otimização', AdSet::getOpcoesOtimizacao(), strtoupper($val('optimization_goal', 'LINK_CLICKS')));
        $this->select('billing_event', 'Cobrança', AdSet::getOpcoesCobranca(), $val('billing_event', 'IMPRESSIONS'));
        $this->select('bid_strategy', 'Estratégia de lance', [
            'LOWEST_COST_WITHOUT_CAP' => 'Custo mais baixo (sem limite de lance)',
            'LOWEST_COST_WITH_BID_CAP' => 'Custo mais baixo com limite de lance',
        ], $val('bid_strategy', 'LOWEST_COST_WITHOUT_CAP'));
        $this->money('daily_budget', 'Orçamento Diário (R$)', $val('daily_budget', ''));
        $this->money('bid_amount', 'Lance (R$)', $val('bid_amount', ''));
        $this->text('age_min', 'Idade mínima', (string) $val('age_min', 18));
        $this->text('age_max', 'Idade máxima', (string) $val('age_max', 65));
        $this->text('country', 'País', $val('country', 'BR'));
        $this->date('start_time', 'Início', $val('start_time', ''));
        $this->date('end_time',   'Término', $val('end_time', ''));

        // $this->submit($adset ? 'Atualizar Conjunto' : 'Criar Conjunto');
    }

    protected function disabledEdit(): array
    {
        return [
            'campaign_id',
            'optimization_goal',
            'billing_event',
            'start_time'
        ];
    }
}
