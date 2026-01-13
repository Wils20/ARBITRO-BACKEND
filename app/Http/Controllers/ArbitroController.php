<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RabbitMQService;
use App\Models\Partido;
use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <--- IMPRESCINDIBLE para el reinicio

class ArbitroController extends Controller
{
    protected $mqService;

    public function __construct(RabbitMQService $service)
    {
        $this->mqService = $service;
    }

    /**
     * Muestra el Panel de Control del Árbitro.
     */
    public function panel($id)
    {
        // 1. Buscamos el partido
        $partido = Partido::findOrFail($id);

        // 2. Calculamos el tiempo transcurrido (si está en juego)
        // Esto evita que el reloj empiece en 00:00 si recargas la página.
        $segundos = 0;

        if ($partido->estado == 'en_curso' && $partido->hora_inicio) {
            // Usamos timestamps para evitar errores de zona horaria
            $inicio = Carbon::parse($partido->hora_inicio)->timestamp;
            $ahora = now()->timestamp;
            $segundos = $ahora - $inicio;

            // Seguridad: Si sale negativo, lo dejamos en 0
            if ($segundos < 0) {
                $segundos = 0;
            }
        }

        // Retornamos la vista (asegúrate que tu archivo se llame 'panel.blade.php')
        return view('panel', compact('partido', 'segundos'));
    }

    /**
     * Recibe eventos (Goles, Tarjetas, Inicio, Fin) y los envía a RabbitMQ.
     */
    public function enviarEvento(Request $request)
    {
        $request->validate([
            'partido_id' => 'required',
            'tipo' => 'required',
            'minuto' => 'required'
        ]);

        $data = $request->all();
        $data['timestamp'] = now()->toDateTimeString();

        // 1. GUARDAR EVENTO EN HISTORIAL (Tabla 'eventos')
        // Excluímos INICIO/FIN/ENTRETIEMPO del historial visible si prefieres,
        // pero aquí guardamos todo para tener registro.
        Evento::create([
            'partido_id' => $request->partido_id,
            'tipo' => $request->tipo,
            'minuto' => $request->minuto,
            'descripcion' => $request->jugador ?? '', // Si es gol/tarjeta, aquí va el jugador
            'equipo' => $request->equipo
        ]);

        // 2. ACTUALIZAR EL PARTIDO (Tabla 'partidos')
        $partido = Partido::find($request->partido_id);

        // --- Lógica de Goles ---
        if ($request->tipo === 'GOL') {
            if ($request->equipo == $partido->equipo_local) {
                $partido->goles_local += 1;
            } else {
                $partido->goles_visitante += 1;
            }
        }

        // --- Lógica de Estados y Tiempo ---
        if ($request->tipo === 'INICIO') {
            $partido->estado = 'en_curso';
            // Solo establecemos hora de inicio si está vacía (para no reiniciar reloj si le dan click 2 veces)
            if (!$partido->hora_inicio) {
                $partido->hora_inicio = now();
            }
        } elseif ($request->tipo === 'ENTRETIEMPO') {
            $partido->estado = 'entretiempo';
        } elseif ($request->tipo === 'FIN') {
            $partido->estado = 'finalizado';
        }

        $partido->save(); // Guardar cambios en BD

        // 3. ENVIAR A RABBITMQ (Para que los clientes se actualicen)
        // Enviamos los datos frescos de la BD para asegurar consistencia
        $data['marcador_local'] = $partido->goles_local;
        $data['marcador_visitante'] = $partido->goles_visitante;
        $data['estado'] = $partido->estado;
        // Enviamos la hora de inicio para sincronizar relojes de clientes
        $data['hora_inicio'] = $partido->hora_inicio;

        $routingKey = 'partido.' . $request->partido_id;
        $this->mqService->publish($data, $routingKey);

        return response()->json(['status' => 'ok', 'data' => $data]);
    }

    /**
     * REINICIO TOTAL (FACTORY RESET)
     * Borra goles, historial y reinicia el reloj.
     */
    public function reiniciarPartido($id)
    {
        // 1. RESTAURAR TABLA PARTIDOS
        // Usamos DB::table para una actualización directa y limpia
        DB::table('partidos')
            ->where('id', $id)
            ->update([
                'goles_local' => 0,
                'goles_visitante' => 0,
                'estado' => 'PROGRAMADO',
                'hora_inicio' => null, // Esto reinicia el cronómetro a 00:00
                'updated_at' => now()
            ]);

        // 2. BORRAR HISTORIAL DE EVENTOS
        DB::table('eventos')->where('partido_id', $id)->delete();

        // 3. OPCIONAL: AVISAR A RABBITMQ QUE HUBO UN RESET
        // Esto haría que los clientes se pongan en 0 automáticamente sin recargar
        $this->mqService->publish([
            'tipo' => 'RESET',
            'marcador_local' => 0,
            'marcador_visitante' => 0,
            'estado' => 'PROGRAMADO'
        ], 'partido.' . $id);

        return back()->with('success', 'Partido reiniciado a 0-0 correctamente.');
    }
}
