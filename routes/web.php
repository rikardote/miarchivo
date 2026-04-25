<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', \App\Livewire\Dashboard::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Expedients
    Route::get('/expedients', \App\Livewire\Expedients\Index::class)->name('expedients.index');
    Route::get('/expedients/create', \App\Livewire\Expedients\Create::class)->name('expedients.create');
    Route::get('/expedients/{expedient}', \App\Livewire\Expedients\Show::class)->name('expedients.show');
    Route::get('/expedients/{expedient}/edit', \App\Livewire\Expedients\Edit::class)->name('expedients.edit');
    
    // Loans
    Route::get('/loans', \App\Livewire\Loans\Index::class)->name('loans.index');
    Route::get('/loans/request', \App\Livewire\Loans\Request::class)->name('loans.request');
    Route::get('/loans/{loan}/manage', \App\Livewire\Loans\Manage::class)->name('loans.manage');

    // Employees
    Route::get('/employees', \App\Livewire\Employees\Index::class)->name('employees.index');
    Route::get('/employees/{employee}', \App\Livewire\Employees\Show::class)->name('employees.show');

    // Admin
    Route::get('/locations', \App\Livewire\Locations\Index::class)->name('locations.index');
    Route::get('/users', \App\Livewire\Users\Index::class)->name('users.index');
});

require __DIR__.'/auth.php';
