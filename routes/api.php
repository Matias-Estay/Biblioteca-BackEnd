<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LibraryController;

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
Route::middleware('auth:sanctum')->get('/quota', [AuthController::class, 'GET_quota']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/careateCollection', [LibraryController::class, 'POST_Collection']);
Route::middleware('auth:sanctum')->get('/collections', [LibraryController::class, 'GET_Collections']);
Route::middleware('auth:sanctum')->post('/askQuestion', [LibraryController::class, 'POST_AskQuestion']);

Route::get('/loggedIn', [AuthController::class, 'loggedIn']);
Route::post('/login', [AuthController::class, 'login']);
