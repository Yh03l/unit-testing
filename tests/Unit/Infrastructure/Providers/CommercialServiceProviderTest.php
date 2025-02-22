<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Providers;

use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\LaravelCommandBus;
use Commercial\Infrastructure\Bus\LaravelQueryBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Infrastructure\EventBus\EventBus;
use Commercial\Infrastructure\EventBus\InMemoryEventBus;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentCatalogRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentServiceRepository;
use Commercial\Infrastructure\Providers\CommercialServiceProvider;
use Illuminate\Foundation\Application;
use PHPUnit\Framework\TestCase;

class CommercialServiceProviderTest extends TestCase
{
    private Application $app;
    private CommercialServiceProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->app = $this->createMock(Application::class);
        $this->provider = new CommercialServiceProvider($this->app);
    }

    public function test_register_binds_repositories_in_correct_order(): void
    {
        // Arrange
        $bindOrder = [];
        $this->app->method('bind')
            ->willReturnCallback(function($abstract, $concrete) use (&$bindOrder) {
                $bindOrder[] = $abstract;
            });

        // Act
        $this->provider->register();

        // Assert
        $expectedOrder = [
            CatalogRepository::class,
            ServiceRepository::class,
            LaravelCommandBus::class,
            LaravelQueryBus::class,
            CommandBus::class,
            QueryBus::class
        ];

        foreach ($expectedOrder as $index => $expected) {
            $this->assertEquals($expected, $bindOrder[$index], "Binding at position $index should be $expected");
        }
    }

    public function test_register_creates_command_bus_correctly(): void
    {
        // Arrange
        $actualBindings = [];
        $this->app->method('bind')
            ->willReturnCallback(function($abstract, $concrete) use (&$actualBindings) {
                $actualBindings[$abstract] = $concrete;
            });

        // Act
        $this->provider->register();

        // Assert
        $commandBusClosure = $actualBindings[LaravelCommandBus::class];
        $this->assertTrue(is_callable($commandBusClosure));

        // Verify the closure creates a LaravelCommandBus
        $mockApp = $this->createMock(Application::class);
        $result = $commandBusClosure($mockApp);
        $this->assertInstanceOf(LaravelCommandBus::class, $result);
    }

    public function test_register_creates_query_bus_correctly(): void
    {
        // Arrange
        $actualBindings = [];
        $this->app->method('bind')
            ->willReturnCallback(function($abstract, $concrete) use (&$actualBindings) {
                $actualBindings[$abstract] = $concrete;
            });

        // Act
        $this->provider->register();

        // Assert
        $queryBusClosure = $actualBindings[LaravelQueryBus::class];
        $this->assertTrue(is_callable($queryBusClosure));

        // Verify the closure creates a LaravelQueryBus
        $mockApp = $this->createMock(Application::class);
        $result = $queryBusClosure($mockApp);
        $this->assertInstanceOf(LaravelQueryBus::class, $result);
    }

    public function test_register_binds_event_bus_as_singleton(): void
    {
        // Arrange
        $this->app->expects($this->once())
            ->method('singleton')
            ->with(
                $this->equalTo(EventBus::class),
                $this->equalTo(InMemoryEventBus::class)
            );

        // Act
        $this->provider->register();
    }

    public function test_boot_loads_migrations_with_correct_path(): void
    {
        // Arrange
        $this->app->expects($this->once())
            ->method('runningInConsole')
            ->willReturn(false);

        // Mock the trait method loadMigrationsFrom
        $provider = $this->getMockBuilder(CommercialServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['loadMigrationsFrom'])
            ->getMock();

        $provider->expects($this->once())
            ->method('loadMigrationsFrom')
            ->with($this->callback(function ($paths) {
                return is_array($paths) && 
                       str_contains($paths[0], 'Persistence/Migrations');
            }));

        // Act
        $provider->boot();
    }

    public function test_boot_does_not_publish_migrations_when_not_in_console(): void
    {
        // Arrange
        $this->app->expects($this->once())
            ->method('runningInConsole')
            ->willReturn(false);

        // Mock the trait method publishes
        $provider = $this->getMockBuilder(CommercialServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['publishes'])
            ->getMock();

        $provider->expects($this->never())
            ->method('publishes');

        // Act
        $provider->boot();
    }

    public function test_boot_publishes_migrations_when_running_in_console(): void
    {
        // Arrange
        $this->app->expects($this->once())
            ->method('runningInConsole')
            ->willReturn(true);

        // Mock the trait method publishes and database_path
        $provider = $this->getMockBuilder(CommercialServiceProvider::class)
            ->setConstructorArgs([$this->app])
            ->onlyMethods(['publishes', 'getDatabasePath'])
            ->getMock();

        $provider->method('getDatabasePath')
            ->willReturn('/database/migrations');

        $provider->expects($this->once())
            ->method('publishes')
            ->with(
                $this->callback(function ($paths) {
                    return is_array($paths) && 
                           count($paths) === 1 &&
                           str_contains(key($paths), 'Persistence/Migrations') &&
                           str_contains(current($paths), '/database/migrations');
                }),
                'commercial-migrations'
            );

        // Act
        $provider->boot();
    }

    private function findBinding(array $bindings, string $abstract): ?array
    {
        foreach ($bindings as $binding) {
            if ($binding[0] === $abstract) {
                return $binding;
            }
        }
        return null;
    }
} 