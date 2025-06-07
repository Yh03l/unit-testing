<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserModel extends Model
{
	use HasUuids, SoftDeletes;

	protected $table = 'users';
	protected $fillable = [
		'id',
		'nombre',
		'apellido',
		'email',
		'password',
		'estado',
		'tipo_usuario',
	];

	protected $hidden = ['password', 'remember_token'];

	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	public $incrementing = false;
	protected $keyType = 'string';

	public function patient(): HasOne
	{
		return $this->hasOne(PatientModel::class, 'user_id', 'id');
	}
}
