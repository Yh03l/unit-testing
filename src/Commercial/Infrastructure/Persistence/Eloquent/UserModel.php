<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserModel extends Authenticatable
{
	use HasUuids, Notifiable, SoftDeletes;

	protected $table = 'users';
	protected $primaryKey = 'id';

	protected $fillable = ['nombre', 'apellido', 'email', 'password', 'tipo_usuario', 'estado'];

	protected $hidden = ['password', 'remember_token'];

	protected $casts = [
		'email_verified_at' => 'datetime',
		'password' => 'hashed',
	];

	public function administrator()
	{
		return $this->hasOne(AdministratorModel::class, 'user_id');
	}

	public function patient()
	{
		return $this->hasOne(PatientModel::class, 'user_id');
	}
}
