<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    public function publish($message, $routingKey)
    {
        // 1. Conexión al servidor RabbitMQ (host, puerto, user, pass)
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        // 2. Declaramos el Exchange (Intercambiador)
        // Lo llamamos 'mundial_exchange' y tipo 'topic' para organizar por partidos
        $channel->exchange_declare('mundial_exchange', 'topic', false, true, false);

        // 3. Preparamos el mensaje en formato JSON
        $msg = new AMQPMessage(json_encode($message));

        // 4. Publicamos al exchange con la ruta especifica (ej: partido.1)
        $channel->basic_publish($msg, 'mundial_exchange', $routingKey);

        $channel->close();
        $connection->close();
    }
}
