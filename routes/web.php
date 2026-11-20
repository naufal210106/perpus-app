<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\MemberController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/tentang', [HomeController::class, 'tentang']);
Route::get('/kontak', [HomeController::class, 'kontak']);

// Blok rute yang wajib login
Route::middleware(['auth'])->group(function () {
    // Route AJAX (JSON) HARUS ditulis DI ATAS Route::resource
    Route::get('books/search', [BookController::class, 'search'])
        ->name('books.search');
    Route::get('members/search', [MemberController::class, 'search'])
        ->name('members.search');
    Route::get('members/cek-nis', [MemberController::class, 'cekNis'])
        ->name('members.cek-nis');

    Route::resource('books', BookController::class);
    Route::resource('members', MemberController::class);

    // Bypass error Ziggy untuk Dashboard
    Route::get('/dashboard', function () {
        return inertia('Dashboard');
    })->name('dashboard');

    // Bypass error Ziggy untuk menu Profil di pojok kanan atas
    Route::get('/profile', function () {
        return 'Halaman Profil';
    })->name('profile.edit');
    Route::patch('/profile', function () {})->name('profile.update');
    Route::delete('/profile', function () {})->name('profile.destroy');
});

require __DIR__.'/auth.php';