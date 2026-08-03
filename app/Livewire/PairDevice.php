<?php

namespace App\Livewire;

use App\Services\MobileSyncService;
use Livewire\Component;

/**
 * Mobile-only pairing screen. Shown when the app runs on a phone
 * (System::isMobile()) and no device token is stored yet. The user enters
 * the desktop hub's address and the single-use code shown in desktop
 * Settings → Mobile Devices; on success the token is stored on-device and
 * the first sync pulls the hub's data into the local SQLite.
 */
class PairDevice extends Component
{
    public string $host = '';

    public string $code = '';

    public ?string $error = null;

    public bool $syncing = false;

    public function mount(): void
    {
        // Pre-fill with the last-used host for convenience.
        $this->host = MobileSyncService::pairedHost() ?? '';
    }

    public function pair(MobileSyncService $sync): void
    {
        $this->resetErrorBag();
        $this->error = null;

        $this->validate([
            'host' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $this->syncing = true;

        $result = $sync->pair(trim($this->host), trim($this->code));

        $this->syncing = false;

        if (! $result['ok']) {
            $this->error = $result['error'] ?? 'Pairing failed. Check the address and code.';

            return;
        }

        // Pairing + initial sync succeeded — go to the dashboard.
        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.pair-device')
            ->layout('layouts.app');
    }
}
