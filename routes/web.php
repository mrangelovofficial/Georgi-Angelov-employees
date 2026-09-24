<?php

use App\Http\Controllers\TeamPeriodController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TeamPeriodController::class, 'index'])->name('team-period.index');
Route::post('/team-period/calculate', [TeamPeriodController::class, 'calculate'])->name('team-period.calculate');