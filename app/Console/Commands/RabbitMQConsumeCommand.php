<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;

class RabbitMQConsumeCommand extends Command
{
    // El nombre del comando que escribirás en la terminal
    protected $signature = 'rabbitmq:consume';

    protected $description = 'Escuchar eventos del partido en tiempo real';

    protected $mqService;

    public function __construct(RabbitMQService $service)
    {
        parent::__construct();
        $this->mqService = $service;
    }

    public function handle()
    {
        $this->info('Iniciando escucha de RabbitMQ...');

        try {
            // Usamos la función consume del servicio que creamos en el Paso 1
            $this->mqService->consume(function ($msg) {

                // Convertimos el mensaje de texto a datos reales (Array)
                $data = json_decode($msg->body, true);

                // Mostramos bonito en la pantalla negra
                $this->newLine();
                $this->line("📢 NUEVO EVENTO RECIBIDO:");
                $this->info("   Tipo: " . ($data['tipo'] ?? 'Desconocido'));
                $this->comment("   Minuto: " . ($data['minuto'] ?? '0') . "'");

                if (isset($data['descripcion'])) {
                    $this->line("   Jugador: " . $data['descripcion']);
                }

                $this->error("   Marcador: " . ($data['marcador_local']??0) . " - " . ($data['marcador_visitante']??0));
                $this->line("----------------------------------");
            });

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
