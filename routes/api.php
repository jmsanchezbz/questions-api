<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/authenticated', [AuthController::class, 'authenticated']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/questions', [QuestionController::class, 'find']);
    Route::get('/questions/{id}', [QuestionController::class, 'findById']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);

    Route::get('/stats/themes', [StatsController::class, 'themesStats']);
    Route::get('/stats/themes/questions', [StatsController::class, 'questionsStatsByTheme']);

    Route::post('/trials', [TrialController::class, 'generate']);
    Route::get('/trials', [TrialController::class, 'find']);
    Route::get('/trials/{id}', [TrialController::class, 'findById']);
    Route::put('/trials/{id}', [TrialController::class, 'update']);
    Route::put('/trials/{id}/questions/{idq}', [TrialController::class, 'updateTrialQuestion']);

    Route::get('/users', [UserController::class, 'findAll']);
    Route::get('/users/me', function (Request $request) {
        return $request->user();
    });
    Route::get('/users/{id}', [UserController::class, 'findById']);
    Route::put('/users/{id}', [AuthController::class, 'editProfile']);
});
