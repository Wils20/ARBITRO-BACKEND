<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArbitroController; // <--- No olvides esto

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// TU RUTA:
Route::post('/evento', [ArbitroController::class, 'enviarEvento']);
