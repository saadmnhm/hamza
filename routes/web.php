<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('config', \App\Livewire\Config\Config::class)->name('config');
    Route::get('utilisateur', \App\Livewire\Utilisateurs\Utilisateurs::class)->name('users');
    Route::get('roles/list', \App\Livewire\Roles\RolesList::class)->name('roleslist');
});

require __DIR__.'/settings.php';
