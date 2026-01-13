<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('partidos', function (Blueprint $table) {
        $table->id();
        $table->string('equipo_local');
        $table->string('equipo_visitante');
        $table->integer('goles_local')->default(0);
        $table->integer('goles_visitante')->default(0);
        $table->string('estado')->default('programado'); // programado, en_curso, finalizado
        $table->dateTime('hora_inicio')->nullable(); // Para calcular el cronómetro
        $table->timestamps();
    });

    Schema::create('eventos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('partido_id')->constrained('partidos');
        $table->string('tipo'); // GOL, TARJETA, ETC
        $table->integer('minuto');
        $table->string('descripcion')->nullable();
        $table->string('equipo')->nullable();
        $table->timestamps();
    });
}
};
