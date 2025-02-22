<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrationsTest extends BaseMigrationTest
{
    private array $migrations;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Cargamos las migraciones
        $this->migrations = [
            'users' => require __DIR__ . '/../../../../../src/Commercial/Infrastructure/Persistence/Migrations/2024_12_10_105940_create_users_table.php',
            'catalogs' => require __DIR__ . '/../../../../../src/Commercial/Infrastructure/Persistence/Migrations/2024_12_10_110042_create_catalogo_tables.php',
            'billing' => require __DIR__ . '/../../../../../src/Commercial/Infrastructure/Persistence/Migrations/2024_12_10_110109_create_billing_tables.php'
        ];
    }

    public function test_migrations_extend_migration_class(): void
    {
        foreach ($this->migrations as $migration) {
            $this->assertInstanceOf(Migration::class, $migration);
        }
    }

    public function test_users_migration_has_required_methods(): void
    {
        $migration = $this->migrations['users'];
        $this->assertTrue(method_exists($migration, 'up'));
        $this->assertTrue(method_exists($migration, 'down'));
    }

    public function test_catalog_migration_has_required_methods(): void
    {
        $migration = $this->migrations['catalogs'];
        $this->assertTrue(method_exists($migration, 'up'));
        $this->assertTrue(method_exists($migration, 'down'));
    }

    public function test_billing_migration_has_required_methods(): void
    {
        $migration = $this->migrations['billing'];
        $this->assertTrue(method_exists($migration, 'up'));
        $this->assertTrue(method_exists($migration, 'down'));
    }

    public function test_users_migration_has_expected_table_names(): void
    {
        $reflection = new \ReflectionClass($this->migrations['users']);
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('Schema::table(\'users\'', $source);
        $this->assertStringContainsString('Schema::create(\'administradores\'', $source);
        $this->assertStringContainsString('Schema::create(\'pacientes\'', $source);
    }

    public function test_catalog_migration_has_expected_table_names(): void
    {
        $reflection = new \ReflectionClass($this->migrations['catalogs']);
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('Schema::create(\'catalogos\'', $source);
        $this->assertStringContainsString('Schema::create(\'servicios\'', $source);
        $this->assertStringContainsString('Schema::create(\'costo_servicios\'', $source);
    }

    public function test_billing_migration_has_expected_table_names(): void
    {
        $reflection = new \ReflectionClass($this->migrations['billing']);
        $source = file_get_contents($reflection->getFileName());
        
        $this->assertStringContainsString('Schema::create(\'contratos\'', $source);
        $this->assertStringContainsString('Schema::create(\'fecha_contratos\'', $source);
        $this->assertStringContainsString('Schema::create(\'facturas\'', $source);
    }

    public function test_users_migration_has_expected_columns(): void
    {
        $reflection = new \ReflectionClass($this->migrations['users']);
        $source = file_get_contents($reflection->getFileName());
        
        // Columnas de users
        $this->assertStringContainsString('$table->renameColumn(\'name\', \'nombre\')', $source);
        $this->assertStringContainsString('$table->string(\'apellido\')', $source);
        $this->assertStringContainsString('$table->enum(\'tipo_usuario\'', $source);
        $this->assertStringContainsString('$table->enum(\'estado\'', $source);
        $this->assertStringContainsString('$table->softDeletes()', $source);
        
        // Columnas de administradores
        $this->assertStringContainsString('$table->uuid(\'id\')->primary()', $source);
        $this->assertStringContainsString('$table->foreignUuid(\'user_id\')', $source);
        $this->assertStringContainsString('$table->string(\'cargo\')', $source);
        $this->assertStringContainsString('$table->json(\'permisos\')', $source);
        $this->assertStringContainsString('$table->timestamps()', $source);
        
        // Columnas de pacientes
        $this->assertStringContainsString('$table->uuid(\'id\')->primary()', $source);
        $this->assertStringContainsString('$table->foreignUuid(\'user_id\')', $source);
        $this->assertStringContainsString('$table->date(\'fecha_nacimiento\')', $source);
        $this->assertStringContainsString('$table->timestamps()', $source);
    }

    public function test_catalog_migration_has_expected_columns(): void
    {
        $reflection = new \ReflectionClass($this->migrations['catalogs']);
        $source = file_get_contents($reflection->getFileName());
        
        // Columnas de catalogos
        $this->assertStringContainsString('$table->uuid(\'id\')->primary()', $source);
        $this->assertStringContainsString('$table->string(\'nombre\')', $source);
        $this->assertStringContainsString('$table->string(\'estado\')', $source);
        $this->assertStringContainsString('$table->timestamps()', $source);
        $this->assertStringContainsString('$table->softDeletes()', $source);
        
        // Columnas de servicios
        $this->assertStringContainsString('$table->uuid(\'id\')->primary()', $source);
        $this->assertStringContainsString('$table->string(\'nombre\')', $source);
        $this->assertStringContainsString('$table->text(\'descripcion\')', $source);
        $this->assertStringContainsString('$table->foreignUuid(\'catalogo_id\')', $source);
        
        // Columnas de costo_servicios
        $this->assertStringContainsString('$table->uuid(\'id\')->primary()', $source);
        $this->assertStringContainsString('$table->foreignUuid(\'servicio_id\')', $source);
        $this->assertStringContainsString('$table->decimal(\'monto\'', $source);
        $this->assertStringContainsString('$table->string(\'moneda\')', $source);
        $this->assertStringContainsString('$table->date(\'vigencia\')', $source);
    }

    public function test_billing_migration_has_expected_columns(): void
    {
        $reflection = new \ReflectionClass($this->migrations['billing']);
        $source = file_get_contents($reflection->getFileName());
        
        // Columnas de contratos
        $this->assertStringContainsString('$table->uuid(\'id\')->primary()', $source);
        $this->assertStringContainsString('$table->foreignUuid(\'paciente_id\')', $source);
        $this->assertStringContainsString('$table->foreignUuid(\'servicio_id\')', $source);
        $this->assertStringContainsString('$table->dateTime(\'fecha_inicio\')', $source);
        $this->assertStringContainsString('$table->softDeletes()', $source);
        
        // Columnas de fecha_contratos
        $this->assertStringContainsString('$table->uuid(\'id\')->primary()', $source);
        $this->assertStringContainsString('$table->foreignUuid(\'contrato_id\')', $source);
        $this->assertStringContainsString('$table->date(\'fecha_inicio\')', $source);
        $this->assertStringContainsString('$table->date(\'fecha_fin\')', $source);
        
        // Columnas de facturas
        $this->assertStringContainsString('$table->uuid(\'id\')->primary()', $source);
        $this->assertStringContainsString('$table->foreignUuid(\'contrato_id\')', $source);
        $this->assertStringContainsString('$table->decimal(\'monto_total\'', $source);
        $this->assertStringContainsString('$table->date(\'fecha\')', $source);
        $this->assertStringContainsString('$table->enum(\'estado\'', $source);
    }

    public function test_users_migration_has_expected_foreign_keys(): void
    {
        $reflection = new \ReflectionClass($this->migrations['users']);
        $source = file_get_contents($reflection->getFileName());
        
        // Verificar claves foráneas
        $this->assertStringContainsString('->constrained(\'users\')', $source);
        $this->assertStringContainsString('->onDelete(\'cascade\')', $source);
    }

    public function test_catalog_migration_has_expected_foreign_keys(): void
    {
        $reflection = new \ReflectionClass($this->migrations['catalogs']);
        $source = file_get_contents($reflection->getFileName());
        
        // Verificar claves foráneas
        $this->assertStringContainsString('->constrained(\'catalogos\')', $source);
        $this->assertStringContainsString('->constrained(\'servicios\')', $source);
        $this->assertStringContainsString('->onDelete(\'cascade\')', $source);
    }

    public function test_billing_migration_has_expected_foreign_keys(): void
    {
        $reflection = new \ReflectionClass($this->migrations['billing']);
        $source = file_get_contents($reflection->getFileName());
        
        // Verificar claves foráneas
        $this->assertStringContainsString('->constrained(\'pacientes\')', $source);
        $this->assertStringContainsString('->constrained(\'servicios\')', $source);
        $this->assertStringContainsString('->constrained(\'contratos\')', $source);
    }

    public function test_catalog_migration_has_expected_indexes(): void
    {
        $reflection = new \ReflectionClass($this->migrations['catalogs']);
        $source = file_get_contents($reflection->getFileName());
        
        // Verificar índices
        $this->assertStringContainsString('->index()', $source);
    }

    public function test_billing_migration_has_expected_indexes(): void
    {
        $reflection = new \ReflectionClass($this->migrations['billing']);
        $source = file_get_contents($reflection->getFileName());
        
        // Verificar índices
        $this->assertStringContainsString('->index([\'contrato_id\', \'estado\'])', $source);
    }

    public function test_migrations_have_correct_rollback_order(): void
    {
        $reflection = new \ReflectionClass($this->migrations['users']);
        $source = file_get_contents($reflection->getFileName());
        
        // Verificar orden de eliminación de tablas
        $this->assertMatchesRegularExpression(
            '/dropIfExists\(\'pacientes\'\).*dropIfExists\(\'administradores\'\).*dropIfExists\(\'users\'\)/s',
            $source
        );

        $reflection = new \ReflectionClass($this->migrations['catalogs']);
        $source = file_get_contents($reflection->getFileName());
        
        // Verificar orden de eliminación de tablas
        $this->assertMatchesRegularExpression(
            '/dropIfExists\(\'costo_servicios\'\).*dropIfExists\(\'servicios\'\).*dropIfExists\(\'catalogos\'\)/s',
            $source
        );

        $reflection = new \ReflectionClass($this->migrations['billing']);
        $source = file_get_contents($reflection->getFileName());
        
        // Verificar orden de eliminación de tablas
        $this->assertMatchesRegularExpression(
            '/dropIfExists\(\'facturas\'\).*dropIfExists\(\'fecha_contratos\'\).*dropIfExists\(\'contratos\'\)/s',
            $source
        );
    }

    public function test_users_migration_executes_successfully(): void
    {
        // Crear la tabla users primero (simulando que ya existe)
        $this->schema->create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Ejecutar la migración
        $migration = $this->migrations['users'];
        $migration->up();

        // Verificar que las tablas existen
        $this->assertTrue($this->schema->hasTable('users'));
        $this->assertTrue($this->schema->hasTable('administradores'));
        $this->assertTrue($this->schema->hasTable('pacientes'));

        // Verificar las columnas en users
        $this->assertTrue($this->schema->hasColumn('users', 'nombre'));
        $this->assertTrue($this->schema->hasColumn('users', 'apellido'));
        $this->assertTrue($this->schema->hasColumn('users', 'tipo_usuario'));
        $this->assertTrue($this->schema->hasColumn('users', 'estado'));
        $this->assertTrue($this->schema->hasColumn('users', 'deleted_at'));

        // Verificar las columnas en administradores
        $this->assertTrue($this->schema->hasColumn('administradores', 'id'));
        $this->assertTrue($this->schema->hasColumn('administradores', 'user_id'));
        $this->assertTrue($this->schema->hasColumn('administradores', 'cargo'));
        $this->assertTrue($this->schema->hasColumn('administradores', 'permisos'));
        $this->assertTrue($this->schema->hasColumn('administradores', 'deleted_at'));

        // Verificar las columnas en pacientes
        $this->assertTrue($this->schema->hasColumn('pacientes', 'id'));
        $this->assertTrue($this->schema->hasColumn('pacientes', 'user_id'));
        $this->assertTrue($this->schema->hasColumn('pacientes', 'fecha_nacimiento'));
        $this->assertTrue($this->schema->hasColumn('pacientes', 'deleted_at'));

        // Probar el rollback
        $migration->down();

        // Verificar que las tablas fueron eliminadas
        $this->assertFalse($this->schema->hasTable('pacientes'));
        $this->assertFalse($this->schema->hasTable('administradores'));
        $this->assertFalse($this->schema->hasTable('users'));
    }

    public function test_catalog_migration_executes_successfully(): void
    {
        // Ejecutar la migración
        $migration = $this->migrations['catalogs'];
        $migration->up();

        // Verificar que las tablas existen
        $this->assertTrue($this->schema->hasTable('catalogos'));
        $this->assertTrue($this->schema->hasTable('servicios'));
        $this->assertTrue($this->schema->hasTable('costo_servicios'));

        // Verificar las columnas en catalogos
        $this->assertTrue($this->schema->hasColumn('catalogos', 'id'));
        $this->assertTrue($this->schema->hasColumn('catalogos', 'nombre'));
        $this->assertTrue($this->schema->hasColumn('catalogos', 'estado'));
        $this->assertTrue($this->schema->hasColumn('catalogos', 'deleted_at'));

        // Verificar las columnas en servicios
        $this->assertTrue($this->schema->hasColumn('servicios', 'id'));
        $this->assertTrue($this->schema->hasColumn('servicios', 'nombre'));
        $this->assertTrue($this->schema->hasColumn('servicios', 'descripcion'));
        $this->assertTrue($this->schema->hasColumn('servicios', 'catalogo_id'));

        // Verificar las columnas en costo_servicios
        $this->assertTrue($this->schema->hasColumn('costo_servicios', 'id'));
        $this->assertTrue($this->schema->hasColumn('costo_servicios', 'servicio_id'));
        $this->assertTrue($this->schema->hasColumn('costo_servicios', 'monto'));
        $this->assertTrue($this->schema->hasColumn('costo_servicios', 'moneda'));
        $this->assertTrue($this->schema->hasColumn('costo_servicios', 'vigencia'));

        // Probar el rollback
        $migration->down();

        // Verificar que las tablas fueron eliminadas
        $this->assertFalse($this->schema->hasTable('costo_servicios'));
        $this->assertFalse($this->schema->hasTable('servicios'));
        $this->assertFalse($this->schema->hasTable('catalogos'));
    }

    public function test_billing_migration_executes_successfully(): void
    {
        // Crear las tablas necesarias primero
        $this->schema->create('pacientes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });

        $this->schema->create('servicios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });

        // Ejecutar la migración
        $migration = $this->migrations['billing'];
        $migration->up();

        // Verificar que las tablas existen
        $this->assertTrue($this->schema->hasTable('contratos'));
        $this->assertTrue($this->schema->hasTable('fecha_contratos'));
        $this->assertTrue($this->schema->hasTable('facturas'));

        // Verificar las columnas en contratos
        $this->assertTrue($this->schema->hasColumn('contratos', 'id'));
        $this->assertTrue($this->schema->hasColumn('contratos', 'paciente_id'));
        $this->assertTrue($this->schema->hasColumn('contratos', 'servicio_id'));
        $this->assertTrue($this->schema->hasColumn('contratos', 'fecha_inicio'));
        $this->assertTrue($this->schema->hasColumn('contratos', 'deleted_at'));

        // Verificar las columnas en fecha_contratos
        $this->assertTrue($this->schema->hasColumn('fecha_contratos', 'id'));
        $this->assertTrue($this->schema->hasColumn('fecha_contratos', 'contrato_id'));
        $this->assertTrue($this->schema->hasColumn('fecha_contratos', 'fecha_inicio'));
        $this->assertTrue($this->schema->hasColumn('fecha_contratos', 'fecha_fin'));

        // Verificar las columnas en facturas
        $this->assertTrue($this->schema->hasColumn('facturas', 'id'));
        $this->assertTrue($this->schema->hasColumn('facturas', 'contrato_id'));
        $this->assertTrue($this->schema->hasColumn('facturas', 'monto_total'));
        $this->assertTrue($this->schema->hasColumn('facturas', 'fecha'));
        $this->assertTrue($this->schema->hasColumn('facturas', 'estado'));

        // Probar el rollback
        $migration->down();

        // Verificar que las tablas fueron eliminadas
        $this->assertFalse($this->schema->hasTable('facturas'));
        $this->assertFalse($this->schema->hasTable('fecha_contratos'));
        $this->assertFalse($this->schema->hasTable('contratos'));
    }
} 