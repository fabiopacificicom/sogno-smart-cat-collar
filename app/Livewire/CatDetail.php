<?php

namespace App\Livewire;

use App\Models\Cat;
use App\Models\SensorReading;
use App\Models\Threshold;
use Livewire\Component;

class CatDetail extends Component
{
    public Cat $cat;

    public function mount(Cat $cat): void
    {
        $this->cat = $cat;
    }

    public function render()
    {
        $this->cat->load(['sensorReadings' => fn ($q) => $q->limit(20), 'alerts' => fn ($q) => $q->limit(10)]);

        $latest = $this->cat->latestReading;
        $tempThreshold = Threshold::forCat($this->cat->id, 'temperature');
        $bpmThreshold = Threshold::forCat($this->cat->id, 'bpm');

        // Build simple sparkline data (last 10 readings, oldest first)
        $readings = $this->cat->sensorReadings->reverse()->values()->take(10);
        $tempSpark = $readings->pluck('temperature')->map(fn ($t) => (float) $t)->toArray();
        $bpmSpark = $readings->pluck('bpm')->map(fn ($b) => (int) $b)->toArray();

        return view('livewire.cat-detail', [
            'latest' => $latest,
            'tempThreshold' => $tempThreshold,
            'bpmThreshold' => $bpmThreshold,
            'tempSpark' => $tempSpark,
            'bpmSpark' => $bpmSpark,
        ])
        ->layout('layouts.app');
    }
}
