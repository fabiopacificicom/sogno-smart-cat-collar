<?php

namespace App\Livewire;

use App\Http\Controllers\Api\PairingController;
use App\Models\AppSetting;
use App\Models\Cat;
use App\Models\Device;
use App\Models\ProviderSetting;
use App\Models\Threshold;
use App\Services\ProviderManager;
use Livewire\Component;

class Settings extends Component
{
    public string $activeTab = 'general';

    // General settings
    public string $theme = 'system';
    public string $language = 'en';
    public bool $autoStart = false;
    public bool $startMinimized = false;
    public bool $notificationSound = true;
    public bool $autoDownloadUpdates = true;

    // Provider settings
    public string $activeProvider = 'mock';
    public array $providerSettings = [];

    // Threshold settings
    public float $tempWarning = 39.0;
    public float $tempCritical = 39.5;
    public float $bpmWarning = 220;
    public float $bpmCritical = 250;

    // New cat form
    public string $newCatName = '';
    public string $newCatBreed = 'Domestic Shorthair';

    // Mobile devices (pairing)
    public ?string $pairingCode = null;

    public function mount(): void
    {
        $this->theme = AppSetting::get('theme', 'system');
        $this->language = AppSetting::get('language', 'en');
        $this->autoStart = AppSetting::getBool('auto_start', false);
        $this->startMinimized = AppSetting::getBool('start_minimized', false);
        $this->notificationSound = AppSetting::getBool('notification_sound', true);
        $this->autoDownloadUpdates = AppSetting::getBool('auto_download_updates', true);

        $this->activeProvider = ProviderSetting::activeProvider() ?? 'mock';

        // Load provider settings for all providers
        foreach (app(ProviderManager::class)->all() as $provider) {
            foreach ($provider->getSettingsFields() as $field) {
                $key = $provider->getKey().'.'.$field['key'];
                $this->providerSettings[$key] = ProviderSetting::get($provider->getKey(), $field['key'], '');
            }
        }

        // Load thresholds
        $tempT = Threshold::forCat(null, 'temperature');
        $bpmT = Threshold::forCat(null, 'bpm');
        if ($tempT) {
            $this->tempWarning = $tempT->warning_value;
            $this->tempCritical = $tempT->critical_value;
        }
        if ($bpmT) {
            $this->bpmWarning = $bpmT->warning_value;
            $this->bpmCritical = $bpmT->critical_value;
        }
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveGeneral(): void
    {
        AppSetting::set('theme', $this->theme);
        AppSetting::set('language', $this->language);
        AppSetting::set('auto_start', $this->autoStart ? '1' : '0');
        AppSetting::set('start_minimized', $this->startMinimized ? '1' : '0');
        AppSetting::set('notification_sound', $this->notificationSound ? '1' : '0');
        AppSetting::set('auto_download_updates', $this->autoDownloadUpdates ? '1' : '0');

        $this->dispatch('settings-saved', message: 'General settings saved.');
    }

    public function saveProviders(): void
    {
        foreach ($this->providerSettings as $key => $value) {
            [$provider, $settingKey] = explode('.', $key, 2);
            ProviderSetting::set($provider, $settingKey, $value);
        }

        app(ProviderManager::class)->activate($this->activeProvider);

        $this->dispatch('settings-saved', message: 'Provider settings saved.');
    }

    public function saveThresholds(): void
    {
        $this->validate([
            'tempWarning' => 'required|numeric|lt:tempCritical',
            'tempCritical' => 'required|numeric|gt:tempWarning',
            'bpmWarning' => 'required|numeric|lt:bpmCritical',
            'bpmCritical' => 'required|numeric|gt:bpmWarning',
        ]);

        Threshold::updateOrCreate(
            ['cat_id' => null, 'vital' => 'temperature'],
            ['warning_value' => $this->tempWarning, 'critical_value' => $this->tempCritical],
        );
        Threshold::updateOrCreate(
            ['cat_id' => null, 'vital' => 'bpm'],
            ['warning_value' => $this->bpmWarning, 'critical_value' => $this->bpmCritical],
        );

        $this->dispatch('settings-saved', message: 'Thresholds saved.');
    }

    public function addCat(): void
    {
        $this->validate(['newCatName' => 'required|min:1']);

        Cat::create([
            'name' => trim($this->newCatName),
            'breed' => $this->newCatBreed,
            'status' => 'healthy',
        ]);

        $this->reset('newCatName', 'newCatBreed');
        $this->dispatch('settings-saved', message: 'Cat added.');
    }

    public function deleteCat(int $catId): void
    {
        Cat::find($catId)?->delete();
        $this->dispatch('settings-saved', message: 'Cat removed.');
    }

    /**
     * Generate a fresh single-use pairing code (shown once to the user,
     * who enters it on the phone). Valid for a short TTL.
     */
    public function generatePairingCode(): void
    {
        $this->pairingCode = PairingController::generateCode();
    }

    public function revokeDevice(int $deviceId): void
    {
        Device::find($deviceId)?->revoke();
        $this->dispatch('settings-saved', message: 'Device revoked.');
    }

    public function render()
    {
        $cats = Cat::orderBy('name')->get();
        $providers = app(ProviderManager::class)->all();
        $devices = Device::orderByDesc('paired_at')->get();

        return view('livewire.settings', [
            'cats' => $cats,
            'providers' => $providers,
            'devices' => $devices,
        ])
        ->layout('layouts.app');
    }
}
