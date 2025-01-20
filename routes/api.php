<?php

use App\Http\Controllers\API\SetMenuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('api')->group(function () {
    Route::get('/set-menus', [SetMenuController::class, 'index']);
});