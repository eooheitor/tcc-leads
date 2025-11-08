<?php

namespace App\View\Grids;

use App\Models\AdSet;
use App\View\Base\GridBuilder;
use Illuminate\Support\HtmlString;

class AdSetGrid extends GridBuilder
{
    protected $campaigns;

    public function __construct($adsets, array $campaignOptions = [])
    {
        parent::__construct($adsets);
        $this->campaigns = $campaignOptions;
        $this->setTitle('Conjuntos de Anúncios');
        $this->setFormView(\App\View\Forms\AdSetForm::class);
        $this->setRouteDelete('adsets.destroy');
        $this->setRouteCreate('adsets.create');
        $this->setRouteName('adsets');
        $this->setRouteEdit('adsets.edit');
        $this->setModelName('adsets');

        $this->getColumnsView();
    }

    protected function getColumnsView(): void
    {
        $this->column('name', 'Nome', function ($row) {
            $name = data_get($row, 'name', '—');
            if (mb_strlen($name) > 40) {
                $short = mb_substr($name, 0, 40) . '…';
                return new HtmlString('<span title="' . e($name) . '">' . e($short) . '</span>');
            }
            return e($name);
        });

        $campaignMap = $this->campaigns;
        $this->textTruncateColumn(
            'campaign_id',
            'Campanha',
            $campaignMap,
            40,
            '—',
            true
        );

        $this->column('status', 'Status', function ($row) {
            $status = strtoupper(data_get($row, 'status', '—'));
            $colors = [
                'ACTIVE'   => 'bg-green-100 text-green-700',
                'PAUSED'   => 'bg-yellow-100 text-yellow-700',
                'ARCHIVED' => 'bg-gray-100 text-gray-700',
                'DELETED'  => 'bg-red-100 text-red-700',
            ];
            $cls = $colors[$status] ?? 'bg-gray-50 text-gray-600';
            return new HtmlString("<span class='px-2 py-1 rounded-md text-xs font-semibold {$cls}'>$status</span>");
        });

        $this->column('optimization_goal', 'Otimização', function ($row) {
            $map = AdSet::getOpcoesOtimizacao();
            $val = strtoupper((string) data_get($row, 'optimization_goal', ''));
            return e($map[$val] ?? '—');
        });

        $this->column('billing_event', 'Cobrança', function ($row) {
            $map = AdSet::getOpcoesCobranca();
            $val = strtoupper(data_get($row, 'billing_event', '—'));
            return e($map[$val] ?? '—');
        });

        $this->column('daily_budget', 'Orçamento Diário', function ($row) {
            $val = data_get($row, 'daily_budget');
            if (!$val) return new HtmlString('<span class="text-gray-400 text-sm">—</span>');
            if (is_numeric($val)) {
                $brl = number_format(((int)$val) / 100, 2, ',', '.');
                return "R$ {$brl}";
            }
            return e($val);
        });

        // Lance
        $this->column('bid_amount', 'Lance (R$)', function ($row) {
            $val = data_get($row, 'bid_amount');
            if (!$val) return new HtmlString('<span class="text-gray-400 text-sm">—</span>');
            if (is_numeric($val)) {
                $brl = number_format(((int)$val) / 100, 2, ',', '.');
                return "R$ {$brl}";
            }
            return e($val);
        });

        // Data de início (PT-BR)
        $this->column('start_time', 'Início', function ($row) {
            $val = data_get($row, 'start_time');
            if (!$val) return new HtmlString('<span class="text-gray-400 text-sm">—</span>');
            try {
                return \Carbon\Carbon::parse($val)->format('d/m/Y');
            } catch (\Throwable) {
                return e($val);
            }
        });

        // Data de término (PT-BR)
        $this->column('end_time', 'Término', function ($row) {
            $val = data_get($row, 'end_time');
            if (!$val) return new HtmlString('<span class="text-gray-400 text-sm">—</span>');
            try {
                return \Carbon\Carbon::parse($val)->format('d/m/Y');
            } catch (\Throwable) {
                return e($val);
            }
        });
    }
}
