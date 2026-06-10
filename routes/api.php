<?php

use App\Http\Controllers\AIBuilderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/projects/{project}/chat', [AIBuilderController::class, 'chat'])->middleware('throttle:20,1');
});
