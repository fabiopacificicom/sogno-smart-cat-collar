<?php

use App\Livewire\CatDetail;
use App\Livewire\Dashboard;
use App\Livewire\PairDevice;
use App\Livewire\Settings as SettingsPage;
use App\Livewire\SetupWizard;
use App\Services\MobileSyncService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // On a paired/unpaired phone, the app acts as a sync client: if there's
    // no device token yet, send the user to the pairing screen first.
    if (MobileSyncService::isMobileRuntime() && ! MobileSyncService::isPaired()) {
        return redirect()->route('pair-device');
    }

    // The EnsureSetupCompleted middleware redirects to /setup on first boot.
    // If we reach here, setup is done — go to the dashboard.
    return redirect()->route('dashboard');
});

Route::get('/setup', SetupWizard::class)->name('setup');
Route::get('/pair', PairDevice::class)->name('pair-device');
Route::get('/dashboard', Dashboard::class)->name('dashboard');
Route::get('/cats/{cat}', CatDetail::class)->name('cat-detail');
Route::get('/settings', SettingsPage::class)->name('settings');
