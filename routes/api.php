<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\QuestionController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/books',[BookController::class, 'index']);
Route::get('/books/{id}',[BookController::class, 'show']);
Route::post('/books',[BookController::class, 'store']);
Route::put('/books{id}',[BookController::class, 'update']);
Route::delete('/books/{id}',[BookController::class, 'destroy']);

Route::get('/questions',[QuestionController::class, 'index']);
Route::get('/questions/{id}',[QuestionController::class, 'show']);
Route::put('/questions/{id}',[QuestionController::class, 'udpate']);