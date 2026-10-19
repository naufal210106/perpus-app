<?php 

use Illuminate\Support\Facades\Route; 
use App\Http\Controllers\HomeController; 
use App\Http\Controllers\BookController;

Route::get('/', [HomeController::class, 'index']); 
Route::get('/tentang', [HomeController::class, 'tentang']); 
Route::get('/kontak', [HomeController::class, 'kontak']); 

// Blok rute yang wajib login
Route::middleware(['auth'])->group(function () { 
    Route::resource('books', BookController::class); 
    
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