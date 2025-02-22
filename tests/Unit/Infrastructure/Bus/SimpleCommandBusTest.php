<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Bus;

use Commercial\Infrastructure\Bus\SimpleCommandBus;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SimpleCommandBusTest extends TestCase
{
    private Container|MockObject $container;
    private SimpleCommandBus $commandBus;

    protected function setUp(): void
    {
        $this->container = $this->createMock(Container::class);
        $this->commandBus = new SimpleCommandBus($this->container);
    }

    public function test_dispatch_delegates_to_handle(): void
    {
        // Arrange
        $command = new class() {
            public string $name = 'TestCommand';
        };
        $handler = new class() {
            public bool $handleWasCalled = false;
            public function handle($command): void
            {
                $this->handleWasCalled = true;
            }
        };

        $handlerClass = get_class($command);
        $handlerClass = str_replace('Command', 'Handler', $handlerClass);

        $this->container
            ->expects($this->once())
            ->method('make')
            ->with($this->equalTo($handlerClass))
            ->willReturn($handler);

        // Act
        $this->commandBus->dispatch($command);

        // Assert
        $this->assertTrue($handler->handleWasCalled, 'Handler should have been called');
    }

    public function test_handle_creates_and_executes_handler(): void
    {
        // Arrange
        $command = new class() {
            public string $name = 'TestCommand';
        };
        $handler = new class() {
            public bool $handleWasCalled = false;
            public function handle($command): void
            {
                $this->handleWasCalled = true;
            }
        };

        $handlerClass = get_class($command);
        $handlerClass = str_replace('Command', 'Handler', $handlerClass);

        $this->container
            ->expects($this->once())
            ->method('make')
            ->with($this->equalTo($handlerClass))
            ->willReturn($handler);

        // Act
        $this->commandBus->handle($command);

        // Assert
        $this->assertTrue($handler->handleWasCalled, 'Handler should have been called');
    }

    public function test_handler_class_name_is_correctly_derived(): void
    {
        // Arrange
        $command = new class() {
            public string $name = 'TestCommand';
        };
        $handler = new class() {
            public bool $handleWasCalled = false;
            public function handle($command): void
            {
                $this->handleWasCalled = true;
            }
        };

        $handlerClass = get_class($command);
        $handlerClass = str_replace('Command', 'Handler', $handlerClass);

        $this->container
            ->expects($this->once())
            ->method('make')
            ->with($this->equalTo($handlerClass))
            ->willReturn($handler);

        // Act
        $this->commandBus->handle($command);

        // Assert
        $this->assertTrue($handler->handleWasCalled, 'Handler should have been called');
    }
} 