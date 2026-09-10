<?php

use App\Http\Controllers\Api\AllocationRuleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EnvelopeController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IncomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/income/sueldo', [IncomeController::class, 'sueldo']);
    Route::post('/income/indrive', [IncomeController::class, 'indrive']);
    Route::post('/income/extra', [IncomeController::class, 'extra']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::post('/envelopes', [EnvelopeController::class, 'store']);
    Route::get('/envelopes', [EnvelopeController::class, 'balances']);
    Route::get('/envelopes/{id}/history', [EnvelopeController::class, 'history']);
    Route::patch('/envelopes/{id}/goal', [EnvelopeController::class, 'updateGoal']);
    Route::get('/allocation-rules', [AllocationRuleController::class, 'index']);
    Route::put('/allocation-rules', [AllocationRuleController::class, 'update']);
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
});
