<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdSet extends Model
{
    public static function getOpcoesOtimizacao()
    {
        return [
            'REACH'               => 'Alcance',
            'IMPRESSIONS'         => 'Impressões',
            'LINK_CLICKS'         => 'Cliques no Link',
            'CONVERSIONS'         => 'Conversões',
            'LEAD_GENERATION'     => 'Geração de Leads',
            'OFFSITE_CONVERSIONS' => 'Conversões (Offsite)'
        ];
    }

    public static function getOpcoesCobranca()
    {
        return [
            'IMPRESSIONS' => 'Impressões',
            'CLICKS'      => 'Cliques',
            'LEAD'        => 'Leads',
        ];
    }
}
