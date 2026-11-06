<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index'])->name('books.index');
Route::post('/books', [BookController::class, 'store'])->name('books.store');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::post('/books/{id}/borrow', [BookController::class, 'borrow'])->name('books.borrow');
    Route::post('/books/{id}/reserve', [BookController::class, 'reserve'])->name('books.reserve');
    Route::post('/books/{id}/reservation/cancel', [BookController::class, 'cancelReservation'])->name('books.reservation.cancel');
    Route::post('/books/{id}/renew', [BookController::class, 'renew'])->name('books.renew');
    Route::post('/books/{id}/return', [BookController::class, 'returnBook'])->name('books.return');
});
