<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdministratorModel extends Model
{
	use HasUuids, SoftDeletes;

	protected $table = 'administradores';
	protected $primaryKey = 'id';

	protected $fillable = ['user_id', 'cargo', 'permisos'];

	protected $casts = [
		'permisos' => 'json',
	];

	public function user()
	{
		return $this->belongsTo(UserModel::class, 'user_id');
	}
}
