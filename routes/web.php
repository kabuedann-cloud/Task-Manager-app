<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
Route::get('/tasks/report', [TaskController::class, 'report'])->name('tasks.report');
Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
Route::resource('tasks', TaskController::class)->except(['index']);
