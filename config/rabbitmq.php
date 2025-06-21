<?php

return [
	/*
    |--------------------------------------------------------------------------
    | RabbitMQ Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar las opciones de conexión para RabbitMQ.
    | Si RabbitMQ no está disponible, el sistema usará InMemoryEventBus
    | como fallback.
    |
    */

	'host' => env('RABBITMQ_HOST', 'localhost'),
	'port' => env('RABBITMQ_PORT', 5672),
	'user' => env('RABBITMQ_USER', 'guest'),
	'password' => env('RABBITMQ_PASSWORD', 'guest'),
	'vhost' => env('RABBITMQ_VHOST', '/'),

	'connection_timeout' => env('RABBITMQ_CONNECTION_TIMEOUT', 3.0),
	'read_timeout' => env('RABBITMQ_READ_TIMEOUT', 3.0),
	'write_timeout' => env('RABBITMQ_WRITE_TIMEOUT', 3.0),

	'keepalive' => env('RABBITMQ_KEEPALIVE', false),
	'heartbeat' => env('RABBITMQ_HEARTBEAT', 0),

	/*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para el fallback cuando RabbitMQ no está disponible.
    |
    */

	'use_fallback' => env('RABBITMQ_USE_FALLBACK', true),
	'fallback_to_memory' => env('RABBITMQ_FALLBACK_TO_MEMORY', true),
];
