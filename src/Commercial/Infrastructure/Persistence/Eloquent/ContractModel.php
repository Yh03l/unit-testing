<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ContractModel extends Model
{
	use HasUuids;

	protected $table = 'contratos';
	protected $primaryKey = 'id';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $fillable = [
		'id',
		'paciente_id',
		'servicio_id',
		'plan_alimentario_id',
		'estado',
		'fecha_inicio',
		'fecha_fin',
	];

	protected $casts = [
		'fecha_inicio' => 'datetime',
		'fecha_fin' => 'datetime',
	];
}
