<?php

use App\Http\Controllers\BloodBagController;
use App\Http\Controllers\DistributionLogController;
use Illuminate\Support\Facades\Route;

Route::get('blood-bags', [BloodBagController::class, 'index']);
Route::get('blood-bags/create', [BloodBagController::class, 'create']);
Route::post('blood-bags', [BloodBagController::class, 'store']);
Route::put('blood-bags/{bloodBag}', [BloodBagController::class, 'update']);
Route::patch('blood-bags/{bloodBag}', [BloodBagController::class, 'update']);

Route::get('distribution-logs', [DistributionLogController::class, 'index']);
Route::get('distribution-logs/create', [DistributionLogController::class, 'create']);
Route::post('distribution-logs', [DistributionLogController::class, 'store']);
Route::put('distribution-logs/{distributionLog}', [DistributionLogController::class, 'update']);
Route::patch('distribution-logs/{distributionLog}', [DistributionLogController::class, 'update']);
