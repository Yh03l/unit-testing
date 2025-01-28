<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Commercial\Infrastructure\Persistence\Eloquent\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class ServiceCostModel extends Model
{
    use HasUuid;

    protected $table = 'costo_servicios';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'servicio_id',
        'monto',
        'moneda',
        'vigencia'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'vigencia' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $dates = [
        'vigencia',
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'monto' => '0.00'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (!is_numeric($model->monto)) {
                throw new \InvalidArgumentException('El monto debe ser un número');
            }
            
            if ($model->vigencia < Carbon::now()) {
                throw new \InvalidArgumentException('La fecha de vigencia no puede ser anterior a la fecha actual');
            }
        });
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'servicio_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('vigencia', '>=', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('vigencia', '<', now());
    }

    public function scopeByMoneda(Builder $query, string $moneda): Builder
    {
        return $query->where('moneda', $moneda);
    }

    public function scopeByDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('vigencia', [$from, $to]);
    }

    public function isActive(): bool
    {
        return $this->vigencia >= now();
    }

    public function isExpired(): bool
    {
        return $this->vigencia < now();
    }

    public function getNextActiveCost(): ?self
    {
        return static::where('servicio_id', $this->servicio_id)
            ->where('vigencia', '>', $this->vigencia)
            ->orderBy('vigencia', 'asc')
            ->first();
    }

    public function getPreviousCost(): ?self
    {
        return static::where('servicio_id', $this->servicio_id)
            ->where('vigencia', '<', $this->vigencia)
            ->orderBy('vigencia', 'desc')
            ->first();
    }
}
