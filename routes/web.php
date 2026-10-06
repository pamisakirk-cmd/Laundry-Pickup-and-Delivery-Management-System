<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'Admin'    => redirect()->route('dashboard.admin'),
            'Staff'    => redirect()->route('dashboard.staff'),
            'Customer' => redirect()->route('dashboard.customer'),
            default    => redirect()->route('dashboard.customer'),
        };
    })->name('dashboard');

    Route::get('/dashboard/admin', function () {
        return view('dashboard.admin');
    })->middleware('role:Admin')->name('dashboard.admin');

    Route::get('/dashboard/staff', function () {
        return view('dashboard.staff');
    })->middleware('role:Staff')->name('dashboard.staff');

    Route::get('/dashboard/customer', function () {
        return view('dashboard.customer');
    })->middleware('role:Customer')->name('dashboard.customer');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('users', UserManagementController::class);
});

require __DIR__.'/auth.php';