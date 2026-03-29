<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'tasks'], function () {
    Route::get('/', [TaskController::class, 'index']);
    Route::post('/', [TaskController::class, 'store']);
    Route::get('/report', [TaskController::class, 'report']);
    Route::patch('/{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('/{task}', [TaskController::class, 'destroy']);
});
