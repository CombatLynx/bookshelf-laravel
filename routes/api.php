<?php

use App\Http\Controllers\Api\BookLoanController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/token', [TokenController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::delete('/token', [TokenController::class, 'destroy']);
    Route::post('/books/{id}/borrow', [BookLoanController::class, 'borrow']);
    Route::post('/books/{id}/reserve', [BookLoanController::class, 'reserve']);
    Route::delete('/books/{id}/reservation', [BookLoanController::class, 'cancelReservation']);
    Route::post('/books/{id}/renew', [BookLoanController::class, 'renew']);
    Route::post('/books/{id}/return', [BookLoanController::class, 'returnBook']);
});
