<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RabbitMQService;
use App\Models\Partido;
use App\Models\Evento;
use Carbon\Carbon; // Importante para manejar las fechas

class ArbitroController extends Controller
{
    protected $mqService;

    public function __construct(RabbitMQService $service)
    {
        $this->mqService = $service;
    }

    public function enviarEvento(Request $request)
    {
        $request->validate([
            'partido_id' => 'required',
            'tipo' => 'required',
            'minuto' => 'required'
        ]);

        $data = $request->all();
        // Al tener configurado app.php en 'America/Lima', esto guarda la hora correcta de Perú
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

        // Lógica de Estados (CORREGIDA)
        if ($request->tipo === 'INICIO') {
            $partido->estado = 'en_curso';

            // Solo guardamos la hora de inicio si es la primera vez (para no reiniciar el reloj en el 2do tiempo)
            // O si quieres reiniciar siempre, quita el "if"
            if (!$partido->hora_inicio) {
                $partido->hora_inicio = now();
            }
        }
        elseif ($request->tipo === 'ENTRETIEMPO') {
            // FALTABA ESTO: Actualizar el estado en la DB
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

        // 3. ENVIAR A RABBITMQ (Backend-to-Backend)
        // Esto le avisa al websocket server o a otros servicios
        $routingKey = 'partido.' . $request->partido_id;
        $this->mqService->publish($data, $routingKey);

        return response()->json(['status' => 'ok', 'data' => $data]);
    }

    public function panel()
    {
        // ID FIJO (Cambiar dinámicamente luego si usas URL params)
        $partido_id = 1;

        $partido = Partido::find($partido_id);

        if (!$partido) {
            return "ERROR: No existe el partido ID 1. Créalo en la base de datos.";
        }

        // 3. CÁLCULO PRECIOSO DE SEGUNDOS
        // Esto sincroniza el reloj del árbitro si recarga la página
        $segundos = 0;

        if ($partido->estado == 'en_curso' && $partido->hora_inicio) {
            // Calculamos la diferencia absoluta en segundos entre el inicio y AHORA
            // diffInSeconds devuelve un entero positivo
            $segundos = now()->diffInSeconds(Carbon::parse($partido->hora_inicio));
        }

        // Usamos compact para pasar el OBJETO completo.
        // Así en la vista puedes usar $partido->equipo_local en lugar de variables sueltas.
        return view('panel', compact('partido', 'segundos'));
    }
}
