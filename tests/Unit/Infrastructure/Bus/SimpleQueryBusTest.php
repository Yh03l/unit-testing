<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Bus;

use Commercial\Infrastructure\Bus\SimpleQueryBus;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SimpleQueryBusTest extends TestCase
{
    private Container|MockObject $container;
    private SimpleQueryBus $queryBus;

    protected function setUp(): void
    {
        $this->container = $this->createMock(Container::class);
        $this->queryBus = new SimpleQueryBus($this->container);
    }

    public function test_ask_delegates_to_handle(): void
    {
        // Arrange
        $query = new class() {
            public string $name = 'TestQuery';
        };
        $handler = new class() {
            public bool $handleWasCalled = false;
            public function handle($query): string
            {
                $this->handleWasCalled = true;
                return 'result';
            }
        };

        $handlerClass = get_class($query);
        $handlerClass = str_replace('Query', 'Handler', $handlerClass);

        $this->container
            ->expects($this->once())
            ->method('make')
            ->with($this->equalTo($handlerClass))
            ->willReturn($handler);

        // Act
        $result = $this->queryBus->ask($query);

        // Assert
        $this->assertTrue($handler->handleWasCalled, 'Handler should have been called');
        $this->assertEquals('result', $result);
    }

    public function test_handle_creates_and_executes_handler(): void
    {
        // Arrange
        $query = new class() {
            public string $name = 'TestQuery';
        };
        $handler = new class() {
            public bool $handleWasCalled = false;
            public function handle($query): string
            {
                $this->handleWasCalled = true;
                return 'test_result';
            }
        };

        $handlerClass = get_class($query);
        $handlerClass = str_replace('Query', 'Handler', $handlerClass);

        $this->container
            ->expects($this->once())
            ->method('make')
            ->with($this->equalTo($handlerClass))
            ->willReturn($handler);

        // Act
        $result = $this->queryBus->handle($query);

        // Assert
        $this->assertTrue($handler->handleWasCalled, 'Handler should have been called');
        $this->assertEquals('test_result', $result);
    }

    public function test_handler_class_name_is_correctly_derived(): void
    {
        // Arrange
        $query = new class() {
            public string $name = 'TestQuery';
        };
        $handler = new class() {
            public bool $handleWasCalled = false;
            public function handle($query): string
            {
                $this->handleWasCalled = true;
                return 'result';
            }
        };

        $handlerClass = get_class($query);
        $handlerClass = str_replace('Query', 'Handler', $handlerClass);

        $this->container
            ->expects($this->once())
            ->method('make')
            ->with($this->equalTo($handlerClass))
            ->willReturn($handler);

        // Act
        $result = $this->queryBus->handle($query);

        // Assert
        $this->assertTrue($handler->handleWasCalled, 'Handler should have been called');
        $this->assertEquals('result', $result);
    }
} 