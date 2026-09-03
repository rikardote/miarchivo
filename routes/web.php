<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Dashboard;
use App\Livewire\Expedients\Audit;
use App\Livewire\Expedients\ContinuousCreate;
use App\Livewire\Expedients\Create;
use App\Livewire\Expedients\Edit;
use App\Livewire\Expedients\Index;
use App\Livewire\Expedients\PrintLabel;
use App\Livewire\Expedients\Scanner;
use App\Livewire\Expedients\Show;
use App\Livewire\Loans\BulkRequest;
use App\Livewire\Loans\Dispatch;
use App\Livewire\Loans\Manage;
use App\Livewire\Loans\PickingList;
use App\Livewire\Loans\Request;
use App\Livewire\Reports\Inventory;
use App\Models\Expedient;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', Dashboard::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dedicated Mobile PWA Scanner
    Route::get('/scanner', \App\Livewire\Mobile\Scanner::class)->name('mobile.scanner');

    // Expedients
    Route::get('/expedients/scanner', Scanner::class)->name('expedients.scanner');
    Route::get('/expedients/audit', Audit::class)->name('expedients.audit');
    Route::get('/expedients', Index::class)->name('expedients.index');
    Route::get('/expedients/create/{employee?}', Create::class)->name('expedients.create');
    Route::get('/expedients/continuous-create', ContinuousCreate::class)->name('expedients.continuous-create');
    Route::get('/expedients/find/{code}', function ($code) {
        $expedient = Expedient::where('expedient_code', $code)->first();
        if (! $expedient) {
            return redirect()->route('expedients.index')->with('error', "Expediente no encontrado: {$code}");
        }

        return redirect()->route('expedients.show', $expedient);
    })->name('expedients.find');
    Route::get('/expedients/{expedient}', Show::class)->name('expedients.show');
    Route::get('/expedients/{expedient}/edit', Edit::class)->name('expedients.edit');
    Route::get('/expedients/{expedient}/print', PrintLabel::class)->name('expedients.print');

    // Loans
    Route::get('/loans', App\Livewire\Loans\Index::class)->name('loans.index');
    Route::get('/loans/dispatch', Dispatch::class)->name('loans.dispatch');
    Route::get('/loans/picking-list', PickingList::class)->name('loans.picking-list');
    Route::get('/loans/bulk', BulkRequest::class)->name('loans.bulk');
    Route::get('/loans/request/{expedient?}', Request::class)->name('loans.request');
    Route::get('/loans/{loan}/manage', Manage::class)->name('loans.manage');
    Route::get('/loans/{loan}', Manage::class)->name('loans.show');

    // Employees
    Route::get('/employees', App\Livewire\Employees\Index::class)->name('employees.index');
    Route::get('/employees/{employee}', App\Livewire\Employees\Show::class)->name('employees.show');

    // Admin
    Route::get('/locations', App\Livewire\Locations\Index::class)->name('locations.index');
    Route::get('/reports/inventory', Inventory::class)->name('reports.inventory');
    Route::get('/users', App\Livewire\Users\Index::class)->name('users.index');
});

require __DIR__.'/auth.php';
