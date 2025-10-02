<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MensagemController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/mensagens/{mensagem}/form', [MensagemController::class, 'form'])->name('mensagens.form');
});
