<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FeeTypeController;
use App\Http\Controllers\Api\HouseController;
use App\Http\Controllers\Api\HouseOccupancyController;
use App\Http\Controllers\Api\MonthlyDueController;
use App\Http\Controllers\Api\MonthlyReportController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ResidentController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', DashboardController::class);
Route::get('reports/monthly', MonthlyReportController::class);
Route::get('fee-types', [FeeTypeController::class, 'index']);

Route::apiResource('residents', ResidentController::class);
Route::apiResource('houses', HouseController::class);
Route::post('houses/{house}/occupancies', [HouseOccupancyController::class, 'store']);
Route::delete('houses/{house}/occupancies/current', [HouseOccupancyController::class, 'destroy']);

Route::get('monthly-dues', [MonthlyDueController::class, 'index']);
Route::post('monthly-dues/generate', [MonthlyDueController::class, 'store']);
Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);
Route::apiResource('expenses', ExpenseController::class);
