<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Bus;

use Commercial\Infrastructure\Bus\LaravelQueryBus;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class LaravelQueryBusTest extends MockeryTestCase
{
    private LaravelQueryBus $queryBus;
    private Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = Mockery::mock(Container::class);
        $this->queryBus = new LaravelQueryBus($this->container);
    }

    public function testAskResolvesAndExecutesHandler(): void
    {
        // Crear una query de prueba
        $query = new class {
            public function __construct(public string $data = 'test') {}
        };

        // Crear un handler de prueba
        $handler = new class {
            public function __invoke($query)
            {
                return $query->data;
            }
        };

        // El nombre de la clase del handler debe coincidir con el patrón esperado
        $handlerClass = str_replace('Query', 'Handler', get_class($query));

        // Configurar el mock del container
        $this->container->shouldReceive('make')
            ->once()
            ->with($handlerClass)
            ->andReturn($handler);

        // Ejecutar la query y verificar el resultado
        $result = $this->queryBus->ask($query);
        $this->assertEquals('test', $result);
    }

    public function testAskWithCustomHandler(): void
    {
        // Crear una query personalizada
        $query = new class {
            public function getData(): string
            {
                return 'custom data';
            }
        };

        // Crear un handler personalizado
        $handler = new class {
            public function __invoke($query)
            {
                return $query->getData();
            }
        };

        // Configurar el mock del container
        $this->container->shouldReceive('make')
            ->once()
            ->withAnyArgs()
            ->andReturn($handler);

        // Ejecutar la query y verificar el resultado
        $result = $this->queryBus->ask($query);
        $this->assertEquals('custom data', $result);
    }

    public function testAskWithMultipleQueries(): void
    {
        $queries = [];
        $handlers = [];
        $expectedResults = [];

        // Crear múltiples queries y handlers
        for ($i = 0; $i < 3; $i++) {
            $queries[] = new class($i) {
                public function __construct(public int $id) {}
            };

            $handlers[] = new class {
                public function __invoke($query)
                {
                    return "Result {$query->id}";
                }
            };

            $expectedResults[] = "Result {$i}";
        }

        // Configurar el mock del container para cada query
        foreach ($handlers as $handler) {
            $this->container->shouldReceive('make')
                ->once()
                ->withAnyArgs()
                ->andReturn($handler);
        }

        // Ejecutar todas las queries y verificar los resultados
        foreach ($queries as $index => $query) {
            $result = $this->queryBus->ask($query);
            $this->assertEquals($expectedResults[$index], $result);
        }
    }
} 