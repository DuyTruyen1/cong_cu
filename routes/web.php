<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => '/admin'], function () { //, 'middleware' => 'authadmin'
    Route::get('/', [AdminController::class, 'viewHome']);
    Route::get('/logout', [AdminController::class, 'logout']);
});
