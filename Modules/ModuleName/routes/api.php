<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleName\Http\Controllers\ModuleNameController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('modulenames', ModuleNameController::class)->names('modulename');
});
