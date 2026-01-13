<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\ArbitroController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. RUTA PRINCIPAL (La Portada / Landing Page)
// Muestra la lista de partidos y el destacado
Route::get('/', [PartidoController::class, 'index'])->name('inicio');

// 2. RUTA DETALLE DEL PARTIDO (Vista Cliente)
// Muestra el "Minuto a Minuto" para los hinchas
Route::get('/partido/{id}', [PartidoController::class, 'show'])->name('partido.show');

// 3. RUTAS DEL ÁRBITRO
// Panel de control para gestionar el partido
Route::get('/arbitro', [ArbitroController::class, 'panel'])->name('arbitro.panel');
// API interna para guardar goles, tarjetas, etc.
Route::post('/evento', [ArbitroController::class, 'enviarEvento'])->name('evento.store');
