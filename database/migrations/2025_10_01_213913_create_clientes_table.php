<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // obrigatório
            $table->string('numero'); // obrigatório
            $table->string('edificacao'); // obrigatório
            $table->string('cidade'); // obrigatório
            $table->string('procurava_oque')->nullable();
            $table->string('retorno')->nullable();
            $table->string('temperatura')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
