<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\TrialController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::post('/trials', [TrialController::class, 'generate'])
    ->middleware('auth:sanctum');
Route::get('/trials', [TrialController::class, 'find'])
    ->middleware('auth:sanctum');
Route::get('/trials/{id}', [TrialController::class, 'findById'])
    ->middleware('auth:sanctum');
Route::put('/trials/{id}', [TrialController::class, 'update'])
    ->middleware('auth:sanctum');
Route::put('/trials/{id}/questions/{idq}', [TrialController::class, 'updateTrialQuestion'])
    ->middleware('auth:sanctum');

Route::get('/users/me', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/questions', [QuestionController::class, 'find']);
Route::get('/questions/{id}', [QuestionController::class, 'findById']);
Route::put('/questions/{id}', [QuestionController::class, 'update'])
    ->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);

Route::get('/stats/themes', [StatsController::class, 'themesStats'])
    ->middleware('auth:sanctum');
Route::get('/stats/themes/questions', [StatsController::class, 'questionsStatsByTheme'])
    ->middleware('auth:sanctum');
