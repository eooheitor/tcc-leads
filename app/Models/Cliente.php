<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'numero',
        'edificacao',
        'cidade',
        'procurava_oque',
        'retorno',
        'temperatura',
        'mensagem_id',
    ];

    public function mensagem()
    {
        return $this->belongsTo(\App\Models\Mensagem::class);
    }
}
