<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Commands\CancelContract;

use Commercial\Application\Commands\CancelContract\CancelContractCommand;
use Commercial\Application\Commands\CancelContract\CancelContractHandler;
use Commercial\Domain\Repositories\ContractRepository;
use Commercial\Domain\Aggregates\Contract\Contract;
use Commercial\Infrastructure\EventBus\EventBus;
use Commercial\Domain\Events\ContractCancelled;
use PHPUnit\Framework\TestCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CancelContractHandlerTest extends MockeryTestCase
{
    private CancelContractHandler $handler;
    private ContractRepository $repository;
    private EventBus $eventBus;
    private string $contractId;
    private Contract $contract;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(ContractRepository::class);
        $this->eventBus = Mockery::mock(EventBus::class);
        $this->handler = new CancelContractHandler($this->repository, $this->eventBus);
        $this->contractId = 'contract-123';
        $this->contract = Mockery::mock(Contract::class);
    }

    public function testHandleCancelsContractSuccessfully(): void
    {
        // Arrange
        $command = new CancelContractCommand($this->contractId);
        $event = new ContractCancelled($this->contractId);

        $this->repository->shouldReceive('findById')
            ->with($this->contractId)
            ->once()
            ->andReturn($this->contract);

        $this->contract->shouldReceive('cancelarContrato')
            ->once();

        $this->contract->shouldReceive('getEvents')
            ->once()
            ->andReturn([$event]);

        $this->eventBus->shouldReceive('publish')
            ->with($event)
            ->once();

        $this->contract->shouldReceive('clearEvents')
            ->once();

        $this->repository->shouldReceive('save')
            ->with($this->contract)
            ->once();

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
    }

    public function testHandleThrowsExceptionWhenContractNotFound(): void
    {
        // Arrange
        $command = new CancelContractCommand($this->contractId);

        $this->repository->shouldReceive('findById')
            ->with($this->contractId)
            ->once()
            ->andReturn(null);

        // Assert
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Contrato no encontrado');

        // Act
        $this->handler->handle($command);
    }

    public function testHandlePublishesMultipleEvents(): void
    {
        // Arrange
        $command = new CancelContractCommand($this->contractId);
        $event1 = new ContractCancelled($this->contractId);
        $event2 = new ContractCancelled($this->contractId);

        $this->repository->shouldReceive('findById')
            ->with($this->contractId)
            ->once()
            ->andReturn($this->contract);

        $this->contract->shouldReceive('cancelarContrato')
            ->once();

        $this->contract->shouldReceive('getEvents')
            ->once()
            ->andReturn([$event1, $event2]);

        $this->eventBus->shouldReceive('publish')
            ->with($event1)
            ->once();

        $this->eventBus->shouldReceive('publish')
            ->with($event2)
            ->once();

        $this->contract->shouldReceive('clearEvents')
            ->once();

        $this->repository->shouldReceive('save')
            ->with($this->contract)
            ->once();

        // Act
        $this->handler->handle($command);

        // Assert
        $this->assertTrue(true); // Si llegamos aquí, no se lanzaron excepciones
    }
} 