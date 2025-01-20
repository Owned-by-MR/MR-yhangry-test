<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('app');
});
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');
