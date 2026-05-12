<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DailyLogController;
use App\Http\Controllers\Api\FoodEntryController;
use App\Http\Controllers\Api\RoutineController;
use App\Http\Controllers\Api\WorkoutSessionController;
use App\Http\Controllers\Api\BodyMeasurementController;
use App\Http\Controllers\Api\WaterLogController;
use App\Http\Controllers\Api\UserGoalController;
use App\Http\Controllers\Api\ExerciseLogController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Nutricion
    Route::get('/logs',        [DailyLogController::class, 'index']);
    Route::post('/logs',       [DailyLogController::class, 'store']);
    Route::get('/logs/{date}', [DailyLogController::class, 'show']);

    Route::post('/entries',               [FoodEntryController::class, 'store']);
    Route::put('/entries/{foodEntry}',    [FoodEntryController::class, 'update']);
    Route::delete('/entries/{foodEntry}', [FoodEntryController::class, 'destroy']);

    // Agua
    Route::get('/water/{date}', [WaterLogController::class, 'show']);
    Route::post('/water',       [WaterLogController::class, 'upsert']);

    // Metas
    Route::get('/goals',  [UserGoalController::class, 'show']);
    Route::post('/goals', [UserGoalController::class, 'update']);

    // Rutinas
    Route::get('/routines',              [RoutineController::class, 'index']);
    Route::post('/routines',             [RoutineController::class, 'store']);
    Route::put('/routines/{routine}',    [RoutineController::class, 'update']);
    Route::delete('/routines/{routine}', [RoutineController::class, 'destroy']);

    // Sesiones
    Route::get('/sessions',                     [WorkoutSessionController::class, 'index']);
    Route::post('/sessions',                    [WorkoutSessionController::class, 'store']);
    Route::get('/sessions/{workoutSession}',    [WorkoutSessionController::class, 'show']);
    Route::delete('/sessions/{workoutSession}', [WorkoutSessionController::class, 'destroy']);

    // Logs de ejercicio por sesion
    Route::get('/sessions/{workoutSession}/logs', [ExerciseLogController::class, 'bySession']);
    Route::post('/exercise-logs',                 [ExerciseLogController::class, 'store']);
    Route::put('/exercise-logs/{exerciseLog}',    [ExerciseLogController::class, 'update']);
    Route::delete('/exercise-logs/{exerciseLog}', [ExerciseLogController::class, 'destroy']);
    Route::get('/exercise-stats/{exerciseName}',  [ExerciseLogController::class, 'stats']);

    // Medidas corporales
    Route::get('/measurements',                      [BodyMeasurementController::class, 'index']);
    Route::post('/measurements',                     [BodyMeasurementController::class, 'store']);
    Route::delete('/measurements/{bodyMeasurement}', [BodyMeasurementController::class, 'destroy']);
});