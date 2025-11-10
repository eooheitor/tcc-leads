<?php

namespace App\View\Grids;

use App\View\Base\GridBuilder;

class AnuncioGrid extends GridBuilder
{
    protected $campaigns;

    public function __construct($anuncios, $campaignOptions = [])
    {
        parent::__construct($anuncios);
        $this->campaigns = $campaignOptions;
        $this->setTitle('Anúncios');
        $this->setFormView(\App\View\Forms\AnuncioForm::class);
        $this->setRouteDelete('anuncios.destroy');
        $this->setRouteCreate('anuncios.store');
        $this->setRouteEdit('anuncios.edit');
        $this->setModelName('anuncios');
        $this->setRouteName('anuncios');

        $this->getColumnsView();
    }

    protected function getColumnsView()
    {
        $this->column('name', 'Nome');

        if (method_exists($this, 'statusColumn')) {
            $this->statusColumn('status', 'Status');
        } else {
            // fallback sem badge
            $this->enumColumn('status', 'Status', [
                'ACTIVE'   => 'Ativo',
                'PAUSED'   => 'Pausado',
                'ARCHIVED' => 'Arquivado',
                'DELETED'  => 'Deletado',
            ]);
        }

        $campaignMap = $this->campaigns;
        $this->textTruncateColumn(
            'campaign_id', 
            'Campanha',      
            $campaignMap,     
            40,              
            '—',               
            true               
        );

        // Imagem do criativo
        $this->imageColumn('creative.image_url', 'Imagem do Anúncio', 60);

        // Tipo de destino (normalize “UNDEFINED”/vazio => “—”)
        $this->enumColumn('destination_type', 'Tipo de destino', [
            'WEBSITE'   => 'Website',
            'APP'       => 'Aplicativo',
            'MESSENGER' => 'Messenger',
        ]);

        // Otimização (PT-BR, cobre OFFSITE_CONVERSIONS também)
        $this->enumColumn('optimization_goal', 'Otimização', [
            'LEAD_GENERATION'     => 'Geração de Leads',
            'LINK_CLICKS'         => 'Cliques no Link',
            'CONVERSIONS'         => 'Conversões',
            'IMPRESSIONS'         => 'Impressões',
            'OFFSITE_CONVERSIONS' => 'Conversões (fora do site)',
        ]);

        // Lance em centavos -> R$ X,XX
        $this->moneyCentsColumn('bid_amount', 'Lance (R$)');

        // Início -> d/m/Y (ignora hora)
        $this->dateColumn('start_time', 'Início', 'd/m/Y', config('app.timezone'));
    }
}
