<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientModel extends Model
{
	use HasUuids, SoftDeletes;

	protected $table = 'pacientes';
	protected $primaryKey = 'id';

	protected $fillable = ['id', 'user_id', 'fecha_nacimiento', 'genero', 'direccion', 'telefono'];

	protected $casts = [
		'fecha_nacimiento' => 'date',
	];

	public $incrementing = false;
	protected $keyType = 'string';

	public function user(): BelongsTo
	{
		return $this->belongsTo(UserModel::class, 'user_id', 'id');
	}
}
