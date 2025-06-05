<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientModel extends Model
{
	use HasUuids, SoftDeletes;

	protected $table = 'pacientes';
	protected $primaryKey = 'id';

	protected $fillable = ['user_id', 'fecha_nacimiento'];

	protected $casts = [
		'fecha_nacimiento' => 'date',
	];

	public function user()
	{
		return $this->belongsTo(UserModel::class, 'user_id');
	}
}
