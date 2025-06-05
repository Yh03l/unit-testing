<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PlanAlimentarioModel extends Model
{
	use HasUuids;

	protected $table = 'planes_alimentarios';
	protected $primaryKey = 'id';

	protected $fillable = ['id', 'nombre', 'tipo', 'cantidad_dias'];

	// Deshabilitamos el incrementing ya que usamos UUID
	public $incrementing = false;

	// Especificamos el tipo de la llave primaria
	protected $keyType = 'string';

	// Cast automático de atributos
	protected $casts = [
		'cantidad_dias' => 'integer',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];
}
