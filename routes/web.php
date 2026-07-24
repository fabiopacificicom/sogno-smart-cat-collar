<?php

use App\Livewire\CatDetail;
use App\Livewire\Dashboard;
use App\Livewire\Settings as SettingsPage;
use App\Livewire\SetupWizard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // The EnsureSetupCompleted middleware redirects to /setup on first boot.
    // If we reach here, setup is done — go to the dashboard.
    return redirect()->route('dashboard');
});

Route::get('/setup', SetupWizard::class)->name('setup');
Route::get('/dashboard', Dashboard::class)->name('dashboard');
Route::get('/cats/{cat}', CatDetail::class)->name('cat-detail');
Route::get('/settings', SettingsPage::class)->name('settings');
