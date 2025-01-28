<?php

declare(strict_types=1);

namespace Commercial\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Commercial\Domain\Repositories\CatalogRepository;
use Commercial\Domain\Repositories\ServiceRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentCatalogRepository;
use Commercial\Infrastructure\Persistence\Eloquent\EloquentServiceRepository;
use Commercial\Infrastructure\Bus\CommandBus;
use Commercial\Infrastructure\Bus\QueryBus;
use Commercial\Infrastructure\Bus\LaravelCommandBus;
use Commercial\Infrastructure\Bus\LaravelQueryBus;
use Commercial\Infrastructure\EventBus\EventBus;
use Commercial\Infrastructure\EventBus\InMemoryEventBus;

class CommercialServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar repositorios
        $this->app->bind(
            CatalogRepository::class,
            EloquentCatalogRepository::class
        );

        $this->app->bind(
            ServiceRepository::class,
            EloquentServiceRepository::class
        );

        // Registrar buses
        $this->app->bind(LaravelCommandBus::class, function ($app) {
            return new LaravelCommandBus($app);
        });

        $this->app->bind(LaravelQueryBus::class, function ($app) {
            return new LaravelQueryBus($app);
        });

        $this->app->bind(CommandBus::class, LaravelCommandBus::class);
        $this->app->bind(QueryBus::class, LaravelQueryBus::class);

        // Registrar EventBus
        $this->app->singleton(EventBus::class, InMemoryEventBus::class);
    }

    public function boot(): void
    {
        // Registrar las migraciones
        $this->loadMigrationsFrom([
            __DIR__.'/../Persistence/Migrations'
        ]);

        // Publicar las migraciones si es necesario
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Persistence/Migrations' => database_path('migrations'),
            ], 'commercial-migrations');
        }
    }
} 