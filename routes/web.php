<?php

use App\Http\Controllers\AdSetController;
use App\Http\Controllers\AnuncioController;
use App\Http\Controllers\CampanhaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MensagemController;
use Illuminate\Support\Facades\Route;


// Home padrão para autenticados
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('/');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/home', [HomeController::class, 'index']);

    Route::resource('mensagens', MensagemController::class);
    Route::get('mensagens/form/create', [MensagemController::class, 'formCreate'])->name('mensagens.form.create');
    Route::get('mensagens/form/{id}/edit', [MensagemController::class, 'formEdit'])->name('mensagens.form.edit');

    Route::resource('clientes', ClienteController::class);
    Route::get('clientes/form/create', [ClienteController::class, 'formCreate'])->name('clientes.form.create');
    Route::get('clientes/form/{id}/edit', [ClienteController::class, 'formEdit'])->name('clientes.form.edit');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('campanhas', CampanhaController::class);
    Route::get('campanhas/form/create', [CampanhaController::class, 'formCreate'])->name('campanhas.form.create');
    Route::get('campanhas/form/{id}/edit', [CampanhaController::class, 'formEdit'])->name('campanhas.form.edit');
    
    Route::resource('anuncios', AnuncioController::class);

    Route::resource('adsets', AdSetController::class);
    Route::get('adsets/form/create', [AdSetController::class, 'formCreate'])->name('adsets.form.create');
    Route::get('adsets/form/{id}/edit', [AdSetController::class, 'formEdit'])->name('adsets.form.edit');

    Route::fallback([HomeController::class, 'index']);
});

// Se não autenticado, fallback para login
Route::fallback(function () {
    return redirect('/login');
});

require __DIR__ . '/auth.php';
