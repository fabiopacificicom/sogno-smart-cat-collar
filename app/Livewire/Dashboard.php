<?php

namespace App\Livewire;

use App\Models\Alert;
use App\Models\Cat;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        try {
            $cats = Cat::with('latestReading')->orderBy('name')->get();
            $activeAlerts = Alert::with('cat')->active()->latest()->limit(10)->get();
            $readingsToday = \App\Models\SensorReading::whereDate('read_at', today())->count();
        } catch (\Throwable) {
            // Database not ready — show empty state
            $cats = collect();
            $activeAlerts = collect();
            $readingsToday = 0;
        }

        return view('livewire.dashboard', [
            'cats' => $cats,
            'activeAlerts' => $activeAlerts,
            'readingsToday' => $readingsToday,
            'criticalCount' => $cats->where('status', 'critical')->count(),
            'warningCount' => $cats->where('status', 'warning')->count(),
        ])
        ->layout('layouts.app');
    }
}
