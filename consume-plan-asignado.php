<?php
error_reporting(E_ALL & ~E_DEPRECATED);

// Cargar el framework
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Inicializar el kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Commercial\Infrastructure\MessageBroker\RabbitMQ\PlanAlimentarioAsignadoConsumer;
use Commercial\Application\Commands\AssignPlanAlimentario\AssignPlanAlimentarioHandler;
use Illuminate\Support\Facades\Log;

// Usar las variables de entorno configuradas en docker-compose
$connection = new AMQPStreamConnection(
	env('RABBITMQ_HOST', 'rabbitmq'),
	(int) env('RABBITMQ_PORT', 5672),
	env('RABBITMQ_USER', 'admin'),
	env('RABBITMQ_PASSWORD', 'admin'),
	env('RABBITMQ_VHOST', '/')
);

$channel = $connection->channel();

// Usar la cola definida en las definiciones de RabbitMQ
$queue = 'comercial.plan-alimentario-asignado';

// Declarar la cola
$channel->queue_declare($queue, false, true, false, false);

echo "Esperando mensajes en la cola: $queue. Presiona CTRL+C para salir\n";

try {
	// Obtener el handler del contenedor
	$handler = $app->make(AssignPlanAlimentarioHandler::class);
	$consumer = new PlanAlimentarioAsignadoConsumer($handler);

	$callback = function ($msg) use ($consumer) {
		try {
			echo 'Mensaje recibido: ' . $msg->body . "\n";
			$consumer($msg);
			echo "Mensaje procesado correctamente\n";
		} catch (\Exception $e) {
			echo 'Error procesando el mensaje: ' . $e->getMessage() . "\n";
			Log::error('Error procesando mensaje de plan alimentario asignado', [
				'error' => $e->getMessage(),
				'payload' => $msg->body,
			]);
		}
	};

	$channel->basic_consume($queue, '', false, true, false, false, $callback);

	echo " [*] Esperando mensajes. Para salir presiona CTRL+C\n";

	while ($connection->isConnected()) {
		try {
			$channel->wait();
		} catch (\ErrorException $e) {
			// Si hay un error de conexión, salimos del bucle
			break;
		}
	}
} catch (\Exception $e) {
	echo 'Error: ' . $e->getMessage() . "\n";
	Log::error('Error en el consumidor de planes alimentarios asignados', [
		'error' => $e->getMessage(),
	]);
} finally {
	$channel->close();
	$connection->close();
}
