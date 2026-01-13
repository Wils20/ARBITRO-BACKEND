<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partido;

class PartidoController extends Controller
{
    // CLIENTE: Solo ve la lista
    public function index()
    {
        $partidos = Partido::orderBy('created_at', 'desc')->get();
        // Esta vista NO tiene botones de crear ni arbitrar
        return view('welcome', compact('partidos'));
    }

    // CLIENTE: Ve el detalle
    public function show($id)
    {
        $partido = Partido::with('eventos')->findOrFail($id);

        // Cálculo de segundos para el reloj
        $segundos = 0;
        if ($partido->estado == 'en_curso' && $partido->hora_inicio) {
            $segundos = $partido->hora_inicio->diffInSeconds(now());
        }

        return view('panel', compact('partido', 'segundos'));
    }

    // ADMIN: Ve el panel de gestión
    public function adminIndex()
    {
        $partidos = Partido::orderBy('created_at', 'desc')->get();
        // Esta vista SÍ tiene formulario y botones de arbitrar
        return view('admin', compact('partidos'));
    }

    // ADMIN: Guarda el nuevo partido
    public function store(Request $request)
    {
        // Validación simple
        $request->validate([
            'equipo_local' => 'required',
            'equipo_visitante' => 'required'
        ]);

        Partido::create([
            'equipo_local' => $request->equipo_local,
            'equipo_visitante' => $request->equipo_visitante,
            'goles_local' => 0,
            'goles_visitante' => 0,
            'estado' => 'PROGRAMADO'
        ]);

        return back()->with('success', 'Partido creado');
    }
}
