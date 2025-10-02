<?php

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
    Route::resource('clientes', ClienteController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Catch-all para rotas não encontradas (autenticado)
    Route::fallback([HomeController::class, 'index']);
});

// Se não autenticado, fallback para login
Route::fallback(function () {
    return redirect('/login');
});

require __DIR__.'/auth.php';
