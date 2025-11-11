<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('edificacao')->nullable()->change();
            $table->string('cidade')->nullable()->change();
            $table->string('procurava_oque')->nullable()->change();
            $table->string('retorno')->nullable()->change();
            $table->string('temperatura', 50)->nullable()->change();
            $table->unsignedBigInteger('mensagem_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('edificacao')->nullable(false)->change();
            $table->string('cidade')->nullable(false)->change();
            $table->string('procurava_oque')->nullable(false)->change();
            $table->string('retorno')->nullable(false)->change();
            $table->string('temperatura', 50)->nullable(false)->change();
            $table->unsignedBigInteger('mensagem_id')->nullable(false)->change();
        });
    }
};
