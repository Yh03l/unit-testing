<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Persistence\Eloquent;

use Commercial\Domain\Aggregates\User\User;
use Commercial\Domain\Aggregates\User\Patient;
use Commercial\Domain\Repositories\UserRepository;
use Commercial\Domain\ValueObjects\Email;
use DateTimeImmutable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EloquentUserRepository implements UserRepository
{
	private UserModel $model;

	public function __construct(UserModel $model)
	{
		$this->model = $model;
	}

	public function findById(string $id): ?User
	{
		$model = UserModel::with('patient')->find($id);

		if (!$model) {
			return null;
		}

		return $this->toDomain($model);
	}

	public function save(User $user): void
	{
		// Generar un password aleatorio temporal
		$temporalPassword = Str::random(12);

		$model = UserModel::updateOrCreate(
			['id' => $user->getId()],
			[
				'nombre' => $user->getNombre(),
				'apellido' => $user->getApellido(),
				'email' => $user->getEmail()->toString(),
				'estado' => $user->getEstado(),
				'tipo_usuario' => $user instanceof Patient ? 'paciente' : 'admin',
				'password' => Hash::make($temporalPassword),
			]
		);

		if ($user instanceof Patient) {
			PatientModel::updateOrCreate(
				['user_id' => $user->getId()],
				[
					'id' => $user->getId(), // Aseguramos que el ID sea el mismo que el user_id
					'fecha_nacimiento' => $user->getFechaNacimiento()->format('Y-m-d'),
					'genero' => $user->getGenero(),
					'direccion' => $user->getDireccion(),
					'telefono' => $user->getTelefono(),
				]
			);
		}

		// Aquí podrías enviar un email al usuario con su contraseña temporal
		// $this->emailService->sendTemporalPassword($user->getEmail(), $temporalPassword);
	}

	public function delete(string $id): void
	{
		$this->model->destroy($id);
	}

	public function findByEmail(Email $email): ?User
	{
		$model = UserModel::with('patient')->where('email', $email->toString())->first();

		if (!$model) {
			return null;
		}

		return $this->toDomain($model);
	}

	public function findAll(): array
	{
		return $this->model->all()->map(fn($model) => $this->toDomain($model))->all();
	}

	public function findAllPatients(?int $limit = null, ?int $offset = null): array
	{
		$query = UserModel::with('patient')->where('tipo_usuario', 'paciente');

		if ($limit !== null) {
			$query->limit($limit);
		}

		if ($offset !== null) {
			$query->offset($offset);
		}

		return $query->get()->map(fn($model) => $this->toDomain($model))->all();
	}

	private function toDomain(UserModel $model): User
	{
		if ($model->tipo_usuario === 'paciente' && $model->patient) {
			return new Patient(
				id: $model->id,
				nombre: $model->nombre,
				apellido: $model->apellido,
				email: new Email($model->email),
				estado: $model->estado,
				fechaNacimiento: new DateTimeImmutable(
					$model->patient->fecha_nacimiento->format('Y-m-d')
				),
				genero: $model->patient->genero,
				direccion: $model->patient->direccion,
				telefono: $model->patient->telefono
			);
		}

		throw new \DomainException('Tipo de usuario no soportado: ' . $model->tipo_usuario);
	}
}
