<?php
error_reporting(E_ALL & ~E_DEPRECATED);

// Cargar el framework
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Inicializar el kernel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpAmqpLib\Connection\AMQPStreamConnection;
use Commercial\Infrastructure\MessageBroker\RabbitMQ\PlanAlimentarioCreatedConsumer;
use Commercial\Application\Commands\CreatePlanAlimentario\CreatePlanAlimentarioCommandHandler;
use Illuminate\Support\Facades\Log;

// Usar las variables de entorno configuradas en docker-compose
$connection = new AMQPStreamConnection(
	env('RABBITMQ_HOST', 'rabbitmq'),
	(int) env('RABBITMQ_PORT', 5672),
	env('RABBITMQ_USER', 'commercial'),
	env('RABBITMQ_PASSWORD', 'commercial123'),
	env('RABBITMQ_VHOST', '/')
);

$channel = $connection->channel();

$queue = 'commercial.listen.nutritional.plan.created';

$channel->queue_declare($queue, false, true, false, false);

echo "Esperando mensajes en la cola: $queue. Presiona CTRL+C para salir\n";

try {
	// Obtener el handler del contenedor
	$handler = $app->make(CreatePlanAlimentarioCommandHandler::class);
	$consumer = new PlanAlimentarioCreatedConsumer($handler);

	$callback = function ($msg) use ($consumer) {
		try {
			echo 'Mensaje recibido: ' . $msg->body . "\n";
			$consumer($msg);
			echo "Mensaje procesado correctamente\n";
		} catch (\Exception $e) {
			echo 'Error procesando el mensaje: ' . $e->getMessage() . "\n";
			Log::error('Error procesando mensaje de plan alimentario', [
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
	Log::error('Error en el consumidor de planes alimentarios', [
		'error' => $e->getMessage(),
	]);
} finally {
	$channel->close();
	$connection->close();
}
