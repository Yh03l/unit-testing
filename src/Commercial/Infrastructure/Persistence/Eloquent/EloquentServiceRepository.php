<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Domain\Aggregates\Catalog\Service;
use Commercial\Domain\ValueObjects\ServiceStatus;
use Commercial\Domain\ValueObjects\ServiceCost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EloquentServiceRepository implements ServiceRepository
{
    public function save(Service $service): void
    {
        DB::transaction(function () use ($service) {
            $model = ServiceModel::findOrNew($service->getId());
            
            $model->fill([
                'id' => $service->getId(),
                'nombre' => $service->getNombre(),
                'descripcion' => $service->getDescripcion(),
                'tipo_servicio_id' => $service->getTipoServicioId(),
                'estado' => $service->getEstado()->toString(),
                'catalogo_id' => $service->getCatalogoId()
            ]);

            $model->save();

            // Guardar el costo actual solo si es diferente al último registrado
            $lastCost = ServiceCostModel::where('servicio_id', $service->getId())
                ->latest('vigencia')
                ->first();

            $currentCost = $service->getCosto();
            
            if (!$lastCost || 
                $lastCost->monto != $currentCost->getMonto() || 
                $lastCost->moneda != $currentCost->getMoneda()
            ) {
                ServiceCostModel::create([
                    'id' => Str::uuid()->toString(),
                    'servicio_id' => $service->getId(),
                    'monto' => $currentCost->getMonto(),
                    'moneda' => $currentCost->getMoneda(),
                    'vigencia' => $currentCost->getVigencia()
                ]);
            }
        });
    }

    public function findById(string $id): ?Service
    {
        $model = ServiceModel::with('costos')->find($id);

        if (!$model) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function findAll(): array
    {
        return ServiceModel::with('costos')
            ->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function delete(string $id): void
    {
        ServiceModel::destroy($id);
    }

    public function findByStatus(ServiceStatus $status, ?string $catalogId = null): array
    {
        $query = ServiceModel::with('costos')
            ->where('estado', $status->toString());

        if ($catalogId) {
            $query->where('catalogo_id', $catalogId);
        }

        return $query->get()
            ->map(fn($model) => $this->toDomain($model))
            ->all();
    }

    public function getServiceCostHistory(string $serviceId): array
    {
        $costModels = ServiceCostModel::where('servicio_id', $serviceId)
            ->orderBy('vigencia', 'desc')
            ->get();

        return $costModels->map(function ($costModel) {
            return new ServiceCost(
                monto: (float) $costModel->monto,
                moneda: $costModel->moneda,
                vigencia: new \DateTimeImmutable($costModel->vigencia->format('Y-m-d H:i:s'))
            );
        })->all();
    }

    private function toDomain(ServiceModel $model): Service
    {
        $currentCost = $model->costos()
            ->where('vigencia', '>=', now())
            ->orderBy('vigencia', 'asc')
            ->first();

        if (!$currentCost) {
            $currentCost = $model->costos()->latest('vigencia')->first();
        }

        if (!$currentCost) {
            throw new \RuntimeException("No se encontró información de costo para el servicio {$model->id}");
        }

        return new Service(
            id: $model->id,
            nombre: $model->nombre,
            descripcion: $model->descripcion,
            costo: new ServiceCost(
                monto: (float) $currentCost->monto,
                moneda: $currentCost->moneda,
                vigencia: new \DateTimeImmutable($currentCost->vigencia->format('Y-m-d H:i:s'))
            ),
            tipo_servicio_id: $model->tipo_servicio_id,
            estado: ServiceStatus::fromString($model->estado),
            catalogo_id: $model->catalogo_id
        );
    }
} 