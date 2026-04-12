<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CambioPiezaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EscenarioController;
use App\Http\Controllers\Api\EquipoController;
use App\Http\Controllers\Api\EventoController;
use App\Http\Controllers\Api\MantenimientoController;
use App\Http\Controllers\Api\SolicitudController;
use App\Http\Controllers\Api\TecnicoController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── Auth (public) ──────────────────────────────────────────
Route::prefix('v1/auth')->group(function () {
    Route::post('/login',  [AuthController::class, 'login']);
});

// ── Protected ─────────────────────────────────────────────
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/auth/me',     [AuthController::class, 'me']);
    Route::post('/auth/logout',[AuthController::class, 'logout']);

    // Dashboard & Reportes
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/reporte',   [DashboardController::class, 'reporte']);

    // Read-only for all — write restricted per controller via middleware
    Route::apiResource('escenarios',     EscenarioController::class);
    Route::apiResource('equipos',        EquipoController::class);
    Route::apiResource('mantenimientos', MantenimientoController::class);
    Route::post('mantenimientos/{mantenimiento}/fotos',   [MantenimientoController::class, 'uploadFoto']);
    Route::delete('mantenimientos/{mantenimiento}/fotos', [MantenimientoController::class, 'removeFoto']);
    Route::apiResource('eventos',        EventoController::class);
    Route::post('eventos/{evento}/fotos',   [EventoController::class, 'uploadFoto']);
    Route::delete('eventos/{evento}/fotos', [EventoController::class, 'removeFoto']);
    Route::apiResource('tecnicos',       TecnicoController::class);
    Route::apiResource('solicitudes',    SolicitudController::class);
    Route::apiResource('cambios-piezas', CambioPiezaController::class);
    Route::post('cambios-piezas/{cambioPieza}/recepcion', [CambioPiezaController::class, 'confirmarRecepcion']);

    // Users — admin only
    Route::middleware('can:admin')->group(function () {
        Route::get('/users',          [UserController::class, 'index']);
        Route::post('/users',         [UserController::class, 'store']);
        Route::put('/users/{user}',   [UserController::class, 'update']);
        Route::delete('/users/{user}',[UserController::class, 'destroy']);
    });
});
