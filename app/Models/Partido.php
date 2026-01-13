<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional, pero buena práctica ser explícito)
    protected $table = 'partidos';

    // CAMPOS PERMITIDOS PARA GUARDAR (Mass Assignment)
    protected $fillable = [
        'equipo_local',
        'equipo_visitante',
        'goles_local',
        'goles_visitante',
        'estado',       // <--- Vital para que el Javascript sepa si correr o parar
        'hora_inicio'   // <--- Vital para calcular el tiempo transcurrido
    ];

    // CONVERSIONES AUTOMÁTICAS DE TIPOS
    protected $casts = [
        'hora_inicio' => 'datetime', // Laravel lo convertirá automáticamente a Carbon
        'goles_local' => 'integer',
        'goles_visitante' => 'integer',
    ];

    // RELACIÓN: Un partido tiene muchos eventos
    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }
}
