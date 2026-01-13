<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\ArbitroController;

/*
|--------------------------------------------------------------------------
| ZONA PÚBLICA (CLIENTES) - http://127.0.0.1:8001
|--------------------------------------------------------------------------
*/
// Portada: Solo muestra lista de partidos (sin botones de editar)
Route::get('/', [PartidoController::class, 'index'])->name('inicio');

// Vista Detalle: El "Minuto a Minuto" para los hinchas
Route::get('/partido/{id}', [PartidoController::class, 'show'])->name('partido.show');

Route::get('/ir-a-cliente', function () {
    return redirect('http://127.0.0.1:8001');
})->name('inicio');

/*
|--------------------------------------------------------------------------
| ZONA PRIVADA (ADMINISTRADOR) - http://127.0.0.1:8001/admin
|--------------------------------------------------------------------------
*/
// 1. Dashboard Admin: Aquí está el formulario para crear partidos y botones de arbitrar
Route::get('/admin', [PartidoController::class, 'adminIndex'])->name('admin.index');

// 2. Crear Partido: Ruta POST para guardar en la BD
Route::post('/partidos', [PartidoController::class, 'store'])->name('partidos.store');

// 3. Panel del Árbitro: LE AGREGAMOS EL {id} para saber qué partido controlar
Route::get('/arbitro/{id}', [ArbitroController::class, 'panel'])->name('arbitro.panel');

// 4. API Interna (Acciones del juego)
Route::post('/evento', [ArbitroController::class, 'enviarEvento'])->name('evento.store');
Route::post('/partido/{id}/reiniciar', [ArbitroController::class, 'reiniciarPartido'])->name('partido.reiniciar');
