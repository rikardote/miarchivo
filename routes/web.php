<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', \App\Livewire\Dashboard::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Expedients
    Route::get('/expedients/scanner', \App\Livewire\Expedients\Scanner::class)->name('expedients.scanner');
    Route::get('/expedients/audit', \App\Livewire\Expedients\Audit::class)->name('expedients.audit');
    Route::get('/expedients', \App\Livewire\Expedients\Index::class)->name('expedients.index');
    Route::get('/expedients/create/{employee?}', \App\Livewire\Expedients\Create::class)->name('expedients.create');
    Route::get('/expedients/{expedient}', \App\Livewire\Expedients\Show::class)->name('expedients.show');
    Route::get('/expedients/find/{code}', function($code) {
        $expedient = \App\Models\Expedient::where('expedient_code', $code)->first();
        if (!$expedient) {
            return redirect()->route('expedients.index')->with('error', "Expediente no encontrado: {$code}");
        }
        return redirect()->route('expedients.show', $expedient);
    })->name('expedients.find');
    Route::get('/expedients/{expedient}/edit', \App\Livewire\Expedients\Edit::class)->name('expedients.edit');
    Route::get('/expedients/{expedient}/print', \App\Livewire\Expedients\PrintLabel::class)->name('expedients.print');
    
    // Loans
    Route::get('/loans', \App\Livewire\Loans\Index::class)->name('loans.index');
    Route::get('/loans/bulk', \App\Livewire\Loans\BulkRequest::class)->name('loans.bulk');
    Route::get('/loans/request/{expedient?}', \App\Livewire\Loans\Request::class)->name('loans.request');
    Route::get('/loans/{loan}/manage', \App\Livewire\Loans\Manage::class)->name('loans.manage');

    // Employees
    Route::get('/employees', \App\Livewire\Employees\Index::class)->name('employees.index');
    Route::get('/employees/{employee}', \App\Livewire\Employees\Show::class)->name('employees.show');

    // Admin
    Route::get('/locations', \App\Livewire\Locations\Index::class)->name('locations.index');
    Route::get('/reports/inventory', \App\Livewire\Reports\Inventory::class)->name('reports.inventory');
    Route::get('/users', \App\Livewire\Users\Index::class)->name('users.index');
});

require __DIR__.'/auth.php';
