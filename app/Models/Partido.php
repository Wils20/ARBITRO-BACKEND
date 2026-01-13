<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipo_local', 'equipo_visitante',
        'goles_local', 'goles_visitante',
        'estado', 'hora_inicio'
    ];

    public function eventos()
    {
        return $this->hasMany(Evento::class);
    }
}
