<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* |-------------------------------------------------------------------------- | API Routes |-------------------------------------------------------------------------- | | Here is where you can register API routes for your application. These | routes are loaded by the RouteServiceProvider and all of them will | be assigned to the "api" middleware group. Make something great! | */

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
            return $request->user();
        }
        );

        // Wallet & Payroll
        Route::get('/wallet', [\App\Http\Controllers\Api\WalletController::class , 'index']);
        Route::get('/wallet/transactions', [\App\Http\Controllers\Api\WalletController::class , 'transactions']);
        Route::post('/cashbon', [\App\Http\Controllers\Api\CashbonController::class , 'store']);    });
