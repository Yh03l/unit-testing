<?php

declare(strict_types=1);

namespace Commercial\Domain\Aggregates\Contract;

use Commercial\Domain\Events\ContractCreated;
use Commercial\Domain\Events\ContractActivated;
use Commercial\Domain\Events\ContractCancelled;
use Commercial\Domain\Events\CateringContratado;
use Commercial\Domain\ValueObjects\ContractDate;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Enums\TipoServicio;

class Contract
{
	private string $id;
	private string $paciente_id;
	private string $servicio_id;
	private ?string $plan_alimentario_id;
	private ContractDate $fecha_contrato;
	private string $estado;
	private array $events = [];
	private ServiceRepository $serviceRepository;

	private function __construct(
		string $id,
		string $paciente_id,
		string $servicio_id,
		?string $plan_alimentario_id,
		ContractDate $fecha_contrato,
		string $estado,
		ServiceRepository $serviceRepository
	) {
		$this->id = $id;
		$this->paciente_id = $paciente_id;
		$this->servicio_id = $servicio_id;
		$this->plan_alimentario_id = $plan_alimentario_id;
		$this->fecha_contrato = $fecha_contrato;
		$this->estado = $estado;
		$this->serviceRepository = $serviceRepository;
	}

	public static function create(
		string $id,
		string $paciente_id,
		string $servicio_id,
		ContractDate $fecha_contrato,
		ServiceRepository $serviceRepository,
		?string $plan_alimentario_id = null
	): self {
		$contract = new self(
			$id,
			$paciente_id,
			$servicio_id,
			$plan_alimentario_id,
			$fecha_contrato,
			'ACTIVO',
			$serviceRepository
		);

		$contract->addEvent(new ContractCreated($id, $paciente_id, $servicio_id));

		// Si tiene plan alimentario y el servicio es de catering, emitir el evento
		if ($plan_alimentario_id !== null) {
			$contract->emitirEventoCateringContratadoSiAplica($plan_alimentario_id);
		}

		return $contract;
	}

	private function emitirEventoCateringContratadoSiAplica(string $planAlimentarioId): void
	{
		$servicio = $this->serviceRepository->findById($this->servicio_id);
		if ($servicio && $servicio->getTipoServicio() === TipoServicio::CATERING) {
			$this->addEvent(
				new CateringContratado($this->id, $this->paciente_id, $planAlimentarioId)
			);
		}
	}

	public function activarContrato(): void
	{
		if ($this->estado !== 'PENDIENTE') {
			throw new \DomainException('Solo se pueden activar contratos pendientes');
		}

		$this->estado = 'ACTIVO';
		$this->addEvent(new ContractActivated($this->id));
	}

	public function cancelarContrato(): void
	{
		if ($this->estado === 'CANCELADO') {
			throw new \DomainException('El contrato ya está cancelado');
		}

		$this->estado = 'CANCELADO';
		$this->addEvent(new ContractCancelled($this->id));
	}

	public function asignarPlanAlimentario(string $planAlimentarioId): void
	{
		if ($this->estado !== 'ACTIVO') {
			throw new \DomainException(
				'Solo se puede asignar un plan alimentario a contratos activos'
			);
		}

		// Si el plan alimentario ya está asignado, no hacemos nada
		if ($this->plan_alimentario_id === $planAlimentarioId) {
			return;
		}

		$this->plan_alimentario_id = $planAlimentarioId;
		$this->emitirEventoCateringContratadoSiAplica($planAlimentarioId);
	}

	public function generarFactura(): void
	{
		if ($this->estado !== 'ACTIVO') {
			throw new \DomainException('Solo se pueden generar facturas para contratos activos');
		}

		// Lógica para generar factura
		// Esto podría emitir un evento de factura generada
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getPacienteId(): string
	{
		return $this->paciente_id;
	}

	public function getServicioId(): string
	{
		return $this->servicio_id;
	}

	public function getPlanAlimentarioId(): ?string
	{
		return $this->plan_alimentario_id;
	}

	public function getFechaContrato(): ContractDate
	{
		return $this->fecha_contrato;
	}

	public function getEstado(): string
	{
		return $this->estado;
	}

	private function addEvent(object $event): void
	{
		$this->events[] = $event;
	}

	public function getEvents(): array
	{
		return $this->events;
	}

	public function clearEvents(): void
	{
		$this->events = [];
	}
}
