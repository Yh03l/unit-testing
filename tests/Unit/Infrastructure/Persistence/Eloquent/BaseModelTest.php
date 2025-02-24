<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Persistence\Eloquent;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModelTest extends TestCase
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
            'prefix' => '',
            'foreign_key_constraints' => true
        ]);
        
        // Hacer la conexión disponible globalmente
        $this->db->setAsGlobal();
        
        // Iniciar Eloquent
        $this->db->bootEloquent();
        
        // Configurar el constructor de esquema
        $this->schema = $this->db->getDatabaseManager()->getSchemaBuilder();
        
        // Configurar las fachadas de Laravel
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
        $this->app->instance('db', $this->db->getDatabaseManager());
        $this->app->instance('db.schema', $this->schema);

        // Configurar la conexión para los modelos
        Model::setConnectionResolver($this->db->getDatabaseManager());

        // Crear las tablas necesarias
        $this->createTables();
    }

    protected function tearDown(): void
    {
        // Limpiar la base de datos
        $this->schema->dropAllTables();
        
        // Limpiar la instancia de la aplicación
        Container::setInstance(null);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        
        // Limpiar la conexión de los modelos
        Model::unsetConnectionResolver();
        
        parent::tearDown();
    }

    abstract protected function createTables(): void;
} 