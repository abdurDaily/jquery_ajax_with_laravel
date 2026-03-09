<?php

use App\Http\Controllers\Ajax\AjaxControler;
use Illuminate\Support\Facades\Route;

Route::get('/',        [AjaxControler::class, 'index']);
Route::post('/',       [AjaxControler::class, 'store'])->name('store');
Route::put('/{id}',    [AjaxControler::class, 'update'])->name('update');
Route::delete('/{id}', [AjaxControler::class, 'destroy'])->name('destroy');