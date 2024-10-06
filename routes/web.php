<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/{student}/invoice/generate', [\App\Http\Controllers\InvoicesController::class, 'GeneratePdf'])->name('student.invoice.generate');
