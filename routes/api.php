<?php

use App\Http\Controllers\AIBuilderController;
use App\Http\Controllers\RegistryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/projects/{project}/chat', [AIBuilderController::class, 'chat'])->middleware('throttle:20,1');
});

// Central Registry API — only active when this instance is the registry server
if (config('registry.mode') === 'registry') {
    Route::prefix('registry')->middleware('throttle:60,1')->group(function () {
        Route::get('/question-packs',          [RegistryController::class, 'index']);
        Route::get('/question-packs/{slug}',   [RegistryController::class, 'show']);
        Route::get('/updates',                 [RegistryController::class, 'updates']);
        Route::post('/license/verify',         [RegistryController::class, 'verifyLicense']);
    });
}
