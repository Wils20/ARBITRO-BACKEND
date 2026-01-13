<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partido;

class PartidoController extends Controller
{
    // Muestra la portada con todos los partidos
    public function index()
    {
        // Obtenemos todos los partidos
        $partidos = Partido::all();

        // Retornamos la vista 'welcome' (o como hayas llamado a tu HTML principal)
        // pasando la variable $partidos
        return view('welcome', compact('partidos'));
    }

    // Muestra el partido en vivo individual
    public function show($id)
    {
        // Buscamos el partido con sus eventos
        $partido = Partido::with('eventos')->findOrFail($id);

        return view('partido.show', compact('partido'));
    }
}
