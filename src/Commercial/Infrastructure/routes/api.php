<?php

use Illuminate\Support\Facades\Route;
use Commercial\Api\Controllers\ContractController;
use Commercial\Api\Controllers\UserController;
use Commercial\Api\Controllers\CatalogController;
use Commercial\Api\Controllers\ServiceController;

Route::prefix('v1')->group(function () {
    // Rutas de Contratos
    Route::prefix('contracts')->group(function () {
        Route::post('/', [ContractController::class, 'create']);
        Route::get('/{id}', [ContractController::class, 'get']);
        Route::post('/{id}/activate', [ContractController::class, 'activate']);
        Route::post('/{id}/cancel', [ContractController::class, 'cancel']);
        Route::get('/by-paciente/{pacienteId}', [ContractController::class, 'getByPaciente']);
    });

    // Rutas de Usuarios
    Route::prefix('users')->group(function () {
        Route::post('/', [UserController::class, 'create']);
        Route::get('/by-email/{email}', [UserController::class, 'getByEmail']);
    });

    // Rutas para Catálogos
    Route::prefix('catalogs')->group(function () {
        Route::get('/', [CatalogController::class, 'index']);
        Route::get('/{id}', [CatalogController::class, 'show']);
        Route::post('/', [CatalogController::class, 'store']);
        Route::put('/{id}', [CatalogController::class, 'update']);
        Route::delete('/{id}', [CatalogController::class, 'destroy']);
    });

    // Rutas para Servicios
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::get('/{id}', [ServiceController::class, 'show']);
        Route::post('/', [ServiceController::class, 'store']);
        Route::put('/{id}', [ServiceController::class, 'update']);

        // Rutas específicas para gestión de servicios
        Route::put('/{id}/status', [ServiceController::class, 'updateStatus']);
        Route::post('/{id}/costs', [ServiceController::class, 'updateCost']);
        Route::get('/{id}/costs/history', [ServiceController::class, 'getCostHistory']);
    });
});
