<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Commercial\Infrastructure\Persistence\Eloquent\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;

class ServiceCostModel extends Model
{
    use HasUuid;

    private static bool $enableDateValidation = true;

    public static function disableDateValidation(): void
    {
        self::$enableDateValidation = false;
    }

    public static function enableDateValidation(): void
    {
        self::$enableDateValidation = true;
    }

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
        'vigencia' => 'date',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'vigencia'
    ];

    protected $attributes = [
        'monto' => '0.00'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function ($model) {
            $model->validateMonto();
            $model->validateVigenciaDate();
        });
    }

    public function validateVigenciaDate(): void
    {
        if (self::$enableDateValidation && isset($this->vigencia)) {
            $currentDate = Carbon::now()->startOfDay();
            $valueDate = $this->vigencia->copy()->startOfDay();
            
            if ($valueDate->lessThan($currentDate)) {
                throw new \InvalidArgumentException('La fecha de vigencia no puede ser anterior a la fecha actual');
            }
        }
    }

    public function setMontoAttribute($value): void
    {
        if ($value === null || $value === '') {
            throw new \InvalidArgumentException('El monto debe ser un número');
        }

        if (is_bool($value)) {
            throw new \InvalidArgumentException('El monto debe ser un número');
        }

        if (!is_numeric($value)) {
            throw new \InvalidArgumentException('El monto debe ser un número');
        }

        $this->attributes['monto'] = $value;
    }

    public function setVigenciaAttribute($value): void
    {
        if ($value === null) {
            throw new \InvalidArgumentException('La fecha de vigencia no es válida');
        }

        if (is_array($value)) {
            throw new \InvalidArgumentException('La fecha de vigencia no es válida');
        }

        try {
            if (!$value instanceof Carbon) {
                try {
                    $value = Carbon::parse($value);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException('La fecha de vigencia no es válida');
                }
            }

            if (self::$enableDateValidation) {
                $currentDate = Carbon::now()->startOfDay();
                $valueDate = $value->copy()->startOfDay();
                
                if ($valueDate->lessThan($currentDate)) {
                    throw new \InvalidArgumentException('La fecha de vigencia no puede ser anterior a la fecha actual');
                }
            }

            $this->attributes['vigencia'] = $value;
        } catch (\Exception $e) {
            if (!$e instanceof \InvalidArgumentException) {
                throw new \InvalidArgumentException('La fecha de vigencia no es válida');
            }
            throw $e;
        }
    }

    protected function validateMonto(): void
    {
        if (!isset($this->attributes['monto'])) {
            throw new \InvalidArgumentException('El monto debe ser un número');
        }
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'servicio_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('vigencia', '>=', Carbon::now()->startOfDay());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('vigencia', '<', Carbon::now()->startOfDay());
    }

    public function scopeByMoneda(Builder $query, string $moneda): Builder
    {
        return $query->where('moneda', $moneda);
    }

    public function scopeByDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('vigencia', [
            $from->startOfDay(),
            $to->endOfDay()
        ]);
    }

    public function isActive(): bool
    {
        return $this->vigencia >= Carbon::now()->startOfDay();
    }

    public function isExpired(): bool
    {
        return $this->vigencia < Carbon::now()->startOfDay();
    }

    public function getNextActiveCost(): ?self
    {
        return static::where('servicio_id', $this->servicio_id)
            ->where('vigencia', '>', $this->vigencia)
            ->where('monto', '>', $this->monto)
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

    public function getDates()
    {
        return ['created_at', 'updated_at', 'vigencia'];
    }

    public function save(array $options = []): bool
    {
        if (self::$enableDateValidation && isset($this->vigencia)) {
            $currentDate = Carbon::now()->startOfDay();
            $valueDate = $this->vigencia->copy()->startOfDay();
            
            if ($valueDate->lessThan($currentDate)) {
                throw new \InvalidArgumentException('La fecha de vigencia no puede ser anterior a la fecha actual');
            }
        }

        return parent::save($options);
    }
}
