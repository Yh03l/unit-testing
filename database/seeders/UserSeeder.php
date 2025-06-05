<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Commercial\Infrastructure\Persistence\Eloquent\UserModel;
use Commercial\Infrastructure\Persistence\Eloquent\AdministratorModel;
use Commercial\Infrastructure\Persistence\Eloquent\PatientModel;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$totalUserToCreate = 10;

		$faker = \Faker\Factory::create('es_ES');

		// Crear algunos administradores
		for ($i = 0; $i < $totalUserToCreate; $i++) {
			$user = UserModel::create([
				'id' => Str::uuid(),
				'nombre' => $faker->firstName(),
				'apellido' => $faker->lastName(),
				'email' => $faker->unique()->safeEmail(),
				'password' => Hash::make('password'),
				'tipo_usuario' => 'admin',
				'estado' => $faker->randomElement(['activo', 'inactivo', 'suspendido']),
			]);

			AdministratorModel::create([
				'id' => Str::uuid(),
				'user_id' => $user->id,
				'cargo' => $faker->randomElement(['Director', 'Supervisor', 'Coordinador']),
				'permisos' => json_encode(['read', 'write', 'delete']),
			]);
		}

		// Crear algunos pacientes
		for ($i = 0; $i < $totalUserToCreate; $i++) {
			$user = UserModel::create([
				'id' => Str::uuid(),
				'nombre' => $faker->firstName(),
				'apellido' => $faker->lastName(),
				'email' => $faker->unique()->safeEmail(),
				'password' => Hash::make('password'),
				'tipo_usuario' => 'paciente',
				'estado' => $faker->randomElement(['activo', 'inactivo', 'suspendido']),
			]);

			PatientModel::create([
				'id' => Str::uuid(),
				'user_id' => $user->id,
				'fecha_nacimiento' => $faker
					->dateTimeBetween('-80 years', '-18 years')
					->format('Y-m-d'),
			]);
		}
	}
}
