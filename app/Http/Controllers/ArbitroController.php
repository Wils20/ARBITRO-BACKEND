<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RabbitMQService;
use App\Models\Partido;
use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ArbitroController extends Controller
{
    protected $mqService;

    public function __construct(RabbitMQService $service)
    {
        $this->mqService = $service;
    }

    // --- AQUÍ ESTÁ EL CAMBIO IMPORTANTE ---
    // Ahora recibimos el $id como parámetro (viene de la ruta /arbitro/{id})
    public function panel($id)
    {
        // Usamos findOrFail: Si el ID no existe, dará error 404 automáticamente
        $partido = Partido::findOrFail($id);

        // 3. CÁLCULO DE SEGUNDOS
        // Esto sincroniza el reloj del árbitro si recarga la página
        $segundos = 0;

        if ($partido->estado == 'en_curso' && $partido->hora_inicio) {
            // Calculamos la diferencia en segundos entre el inicio y AHORA
            $segundos = now()->diffInSeconds(Carbon::parse($partido->hora_inicio));
        }

        // IMPORTANTE: Asegúrate de que tu archivo de vista se llame 'referee.blade.php'
        // Si tu archivo se llama 'panel.blade.php', cambia 'referee' por 'panel' abajo.
        return view('panel', compact('partido', 'segundos'));
    }

    public function enviarEvento(Request $request)
    {
        $request->validate([
            'partido_id' => 'required',
            'tipo' => 'required',
            'minuto' => 'required'
        ]);

        $data = $request->all();
        $data['timestamp'] = now()->toDateTimeString();

        // 1. GUARDAR EVENTO HISTÓRICO
        Evento::create([
            'partido_id' => $request->partido_id,
            'tipo' => $request->tipo,
            'minuto' => $request->minuto,
            'descripcion' => $request->jugador ?? '',
            'equipo' => $request->equipo
        ]);

        // 2. ACTUALIZAR MARCADOR Y ESTADO DEL PARTIDO
        $partido = Partido::find($request->partido_id);

        // Lógica de Goles
        if ($request->tipo === 'GOL') {
            if ($request->equipo == $partido->equipo_local) {
                $partido->goles_local += 1;
            } else {
                $partido->goles_visitante += 1;
            }
        }

        // Lógica de Estados
        if ($request->tipo === 'INICIO') {
            $partido->estado = 'en_curso';

            // Solo guardamos la hora de inicio si es la primera vez o estaba en null
            if (!$partido->hora_inicio) {
                $partido->hora_inicio = now();
            }
        }
        elseif ($request->tipo === 'ENTRETIEMPO') {
            $partido->estado = 'entretiempo';
        }
        elseif ($request->tipo === 'FIN') {
            $partido->estado = 'finalizado';
        }

        $partido->save(); // Guardamos los cambios

        // Actualizamos la data para RabbitMQ con los valores REALES de la DB
        $data['marcador_local'] = $partido->goles_local;
        $data['marcador_visitante'] = $partido->goles_visitante;
        $data['estado'] = $partido->estado;

        // 3. ENVIAR A RABBITMQ
        $routingKey = 'partido.' . $request->partido_id;
        $this->mqService->publish($data, $routingKey);

        return response()->json(['status' => 'ok', 'data' => $data]);
    }

    public function reiniciarPartido($id)
    {
        // OPCIÓN NUCLEAR: Actualización directa a la base de datos
        DB::table('partidos')
            ->where('id', $id)
            ->update([
                'goles_local' => 0,
                'goles_visitante' => 0,
                'estado' => 'PROGRAMADO',
                'hora_inicio' => null,
                'updated_at' => now()
            ]);

        // Borrar el historial de eventos
        DB::table('eventos')->where('partido_id', $id)->delete();

        return back()->with('success', 'Partido reiniciado a 0-0 correctamente.');
    }
}
