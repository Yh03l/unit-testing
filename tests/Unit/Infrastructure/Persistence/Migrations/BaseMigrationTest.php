<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Migrations;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

abstract class BaseMigrationTest extends TestCase
{
    protected DB $db;
    protected Builder $schema;
    protected Container $app;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar la aplicación Laravel
        $this->app = new Container();
        Container::setInstance($this->app);
        
        // Configurar la conexión a la base de datos en memoria
        $this->db = new DB;
        $this->db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        
        // Obtener el constructor de esquema
        $this->schema = $this->db->getDatabaseManager()->getSchemaBuilder();
        
        // Configurar la fachada Schema
        $this->app->instance('db.schema', $this->schema);
        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        $this->schema->dropAllTables();
        parent::tearDown();
    }
} 