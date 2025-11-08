<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campanha extends Model
{
    public static function getTipoCompra()
    {
        return [
            'AUCTION'  => 'Leilão',
            'RESERVED' => 'Reserva',
        ];
    }

    public static function getObjetivos()
    {
        return [
            'OUTCOME_LEADS'         => 'Leads',
            'OUTCOME_SALES'         => 'Vendas',
            'OUTCOME_ENGAGEMENT'    => 'Engajamento',
            'OUTCOME_AWARENESS'     => 'Reconhecimento (Awareness)',
            'OUTCOME_TRAFFIC'       => 'Tráfego',
            'OUTCOME_APP_PROMOTION' => 'Promoção de App',
        ];
    }

    public static function getCategoriasAnuncios()
    {
        return [
            'NONE'                      => 'Nenhuma',
            'HOUSING'                   => 'Moradia',
            'CREDIT'                    => 'Produtos e serviços financeiros',
            'EMPLOYMENT'                => 'Emprego',
            'ISSUES_ELECTIONS_POLITICS' => 'Questões sociais, eleições ou política',
        ];
    }
}
