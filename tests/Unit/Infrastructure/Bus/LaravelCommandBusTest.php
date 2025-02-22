<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Bus;

use Commercial\Infrastructure\Bus\LaravelCommandBus;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LaravelCommandBusTest extends MockeryTestCase
{
    private LaravelCommandBus $commandBus;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = Mockery::mock(Container::class);
        $this->commandBus = new LaravelCommandBus($this->container);
    }

    public function testDispatchResolvesAndExecutesHandler(): void
    {
        // Crear un comando de prueba
        $command = new class {
            public function __construct(public string $data = 'test') {}
        };

        // Crear un handler de prueba
        $handler = new class {
            public function __invoke($command)
            {
                return $command->data;
            }
        };

        // El nombre de la clase del handler debe coincidir con el patrón esperado
        $handlerClass = str_replace('Command', 'Handler', get_class($command));

        // Configurar el mock del container
        $this->container->shouldReceive('make')
            ->once()
            ->with($handlerClass)
            ->andReturn($handler);

        // Ejecutar el comando
        $this->commandBus->dispatch($command);
    }

    public function testDispatchWithCustomHandler(): void
    {
        // Crear un comando personalizado
        $command = new class {
            public string $executed = 'no';
            public function markAsExecuted(): void
            {
                $this->executed = 'yes';
            }
        };

        // Crear un handler personalizado
        $handler = new class {
            public function __invoke($command)
            {
                $command->markAsExecuted();
            }
        };

        // Configurar el mock del container
        $this->container->shouldReceive('make')
            ->once()
            ->withAnyArgs()
            ->andReturn($handler);

        // Ejecutar el comando
        $this->commandBus->dispatch($command);

        // Verificar que el comando fue ejecutado
        $this->assertEquals('yes', $command->executed);
    }

    public function testDispatchWithMultipleCommands(): void
    {
        $commands = [];
        $handlers = [];

        // Crear múltiples comandos y handlers
        for ($i = 0; $i < 3; $i++) {
            $commands[] = new class($i) {
                public function __construct(public int $id) {}
            };

            $handlers[] = new class {
                public function __invoke($command)
                {
                    return $command->id;
                }
            };
        }

        // Configurar el mock del container para cada comando
        foreach ($handlers as $handler) {
            $this->container->shouldReceive('make')
                ->once()
                ->withAnyArgs()
                ->andReturn($handler);
        }

        // Ejecutar todos los comandos
        foreach ($commands as $command) {
            $this->commandBus->dispatch($command);
        }
    }
} 