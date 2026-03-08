<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleName\Http\Controllers\ModuleNameController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('modulenames', ModuleNameController::class)->names('modulename');
});
