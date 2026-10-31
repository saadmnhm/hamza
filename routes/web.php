<?php

use App\Http\Controllers\DocumentPrintController;
use App\Livewire\Config\Config;
use App\Livewire\Dashboard;
use App\Livewire\Documents\Accounting\Invoice;
use App\Livewire\Documents\DocumentsList;
use App\Livewire\Documents\HR\EmploymentContract;
use App\Livewire\Documents\HR\SalarySlip;
use App\Livewire\Roles\RolesList;
use App\Livewire\Utilisateurs\Utilisateurs;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('configs', Config::class)->name('config');
    Route::get('utilisateur', Utilisateurs::class)->name('users');
    Route::get('roles/list', RolesList::class)->name('roleslist');

    // Document generation routes
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('documents', DocumentsList::class)->name('documents');
        Route::get('documents/emploi-contrat', EmploymentContract::class)->name('documents.employment_contract');
        Route::get('documents/fiche-paie', SalarySlip::class)->name('documents.salary_slip');
        Route::get('documents/facture', Invoice::class)->name('documents.invoice');
        Route::get('documents/print/{history}', [DocumentPrintController::class, 'print'])->name('documents.print');
    });
});
require __DIR__.'/settings.php';
