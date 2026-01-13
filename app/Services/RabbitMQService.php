<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    protected $connection;
    protected $channel;
    protected $exchangeName = 'futbol_exchange'; // El nombre del "centro de distribución"

    public function __construct()
    {
        // Conexión usando las variables de tu archivo .env
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', 'localhost'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest'),
            env('RABBITMQ_VHOST', '/')
        );

        $this->channel = $this->connection->channel();

        // Creamos el Exchange (si no existe)
        $this->channel->exchange_declare(
            $this->exchangeName,
            'topic', // Tipo topic para poder filtrar por partidos
            false,
            true,
            false
        );
    }

    // --- FUNCIÓN 1: PUBLICAR (Usada por el Árbitro) ---
    public function publish($message, $routingKey)
    {
        $jsonMessage = json_encode($message);

        $msg = new AMQPMessage($jsonMessage, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $this->channel->basic_publish($msg, $this->exchangeName, $routingKey);
    }

    // --- FUNCIÓN 2: CONSUMIR (Usada por la Terminal) ---
    public function consume($callback)
    {
        // Nombre de una cola temporal para la consola
        $queueName = 'consola_debug';

        // Declaramos la cola
        $this->channel->queue_declare($queueName, false, false, false, false);

        // Conectamos la cola al Exchange para recibir TODO lo que empiece con "partido."
        $this->channel->queue_bind($queueName, $this->exchangeName, 'partido.#');

        echo " [*] Esperando eventos de fútbol... (Presiona CTRL+C para salir)\n";

        // Ejecutar el callback cuando llegue un mensaje
        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            true, // Auto-Ack (confirmar recibido automáticamente)
            false,
            false,
            $callback
        );

        // Mantener escuchando
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
