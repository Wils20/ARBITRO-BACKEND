<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PartidoController;
use App\Http\Controllers\ArbitroController;

/*
|--------------------------------------------------------------------------
| ZONA PÚBLICA (CLIENTES)
|--------------------------------------------------------------------------
*/

// 1. Portada Principal (Landing Page)
// Muestra la lista de partidos y el destacado
Route::get('/', [PartidoController::class, 'index'])->name('inicio');

// 2. Vista Detalle (Minuto a Minuto para el Hincha)
// Muestra estadísticas, goles y tiempo real
Route::get('/partido/{id}', [PartidoController::class, 'show'])->name('partido.show');

// 3. Redirección Auxiliar (CORREGIDO)
// Cambié el nombre a 'redireccion.cliente' para no chocar con la portada.
// Usamos route() para no depender de http://127.0.0.1 fijo.
Route::get('/ir-a-cliente', function () {
    return redirect()->route('inicio');
})->name('redireccion.cliente');

/*
|--------------------------------------------------------------------------
| ZONA PRIVADA (ADMINISTRADOR)
|--------------------------------------------------------------------------
*/

// 4. Dashboard Admin
// Aquí está el formulario para crear partidos y la lista para elegir cuál arbitrar
Route::get('/admin', [PartidoController::class, 'adminIndex'])->name('admin.index');

// 5. Guardar Nuevo Partido (Acción del formulario de crear)
Route::post('/partidos', [PartidoController::class, 'store'])->name('partidos.store');

/*
|--------------------------------------------------------------------------
| ZONA ÁRBITRO (PANEL DE CONTROL)
|--------------------------------------------------------------------------
*/

// 6. Panel del Árbitro
// Recibe el {id} para saber qué partido se va a controlar
Route::get('/arbitro/{id}', [ArbitroController::class, 'panel'])->name('arbitro.panel');

// 7. API Interna: Guardar Eventos
// Procesa Goles, Tarjetas, Inicio, Fin, Entretiempo
Route::post('/evento', [ArbitroController::class, 'enviarEvento'])->name('evento.store');

// 8. API Interna: REINICIAR PARTIDO (El botón Rojo)
// Esta es la ruta fundamental para que funcione el botón de "Reset"
Route::post('/partido/{id}/reiniciar', [ArbitroController::class, 'reiniciarPartido'])->name('partido.reiniciar');
