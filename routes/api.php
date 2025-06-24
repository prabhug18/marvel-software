<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController;



// Route::get('/ping', fn () => response()->json(['message' => 'API is working']));

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:api')->get('/dashboard', [ReportController::class, 'dashboard']);
Route::middleware('auth:api')->get('/transaction-details', [ReportController::class, 'transactionDetails']);
Route::post('/login', [AuthController::class, 'login']);
