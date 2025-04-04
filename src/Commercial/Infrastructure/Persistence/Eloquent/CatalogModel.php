<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Commercial\Infrastructure\Persistence\Eloquent\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Commercial\Domain\ValueObjects\ServiceStatus;

class CatalogModel extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'catalogos';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nombre',
        'estado'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected static function boot(): void
    {
        parent::boot();
    }

    public function save(array $options = []): bool
    {
        $this->validateState();
        return parent::save($options);
    }

    protected function validateState(): void
    {
        $validStates = [
            ServiceStatus::ACTIVO->toString(),
            ServiceStatus::INACTIVO->toString()
        ];
        
        if (empty($this->estado)) {
            throw new \InvalidArgumentException('El estado del catálogo no puede estar vacío');
        }

        if (!is_string($this->estado)) {
            throw new \InvalidArgumentException('El estado del catálogo debe ser una cadena de texto');
        }

        if (!in_array($this->estado, $validStates, true)) {
            throw new \InvalidArgumentException('Estado de catálogo inválido');
        }
    }

    public function services(): HasMany
    {
        return $this->hasMany(ServiceModel::class, 'catalogo_id');
    }

    public function activeServices(): HasMany
    {
        return $this->services()->where('estado', ServiceStatus::ACTIVO->toString());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('estado', ServiceStatus::ACTIVO->toString());
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('estado', ServiceStatus::INACTIVO->toString());
    }

    public function scopeWithActiveServices(Builder $query): Builder
    {
        return $query->whereHas('services', function ($query) {
            $query->where('estado', ServiceStatus::ACTIVO->toString());
        });
    }

    public function isActive(): bool
    {
        return $this->estado === ServiceStatus::ACTIVO->toString();
    }

    public function canAddServices(): bool
    {
        return $this->isActive();
    }

    public function canUpdateServices(): bool
    {
        return $this->isActive();
    }
}