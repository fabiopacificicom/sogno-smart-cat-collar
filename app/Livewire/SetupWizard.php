<?php

namespace App\Livewire;

use App\Models\AppSetting;
use App\Models\Cat;
use App\Models\ProviderSetting;
use App\Models\Threshold;
use App\Services\ProviderManager;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class SetupWizard extends Component
{
    public int $step = 1;
    public int $totalSteps = 5;

    // Step 2: Cat
    public string $catName = '';
    public string $catBreed = 'Domestic Shorthair';
    public ?int $birthYear = null;

    // Step 3: Provider
    public string $provider = 'mock';

    // Step 4: Thresholds
    public float $tempWarning = 39.0;
    public float $tempCritical = 39.5;
    public float $bpmWarning = 220;
    public float $bpmCritical = 250;

    // Step 5: Seed choice
    public bool $seedDemoData = true;

    public function mount(): void
    {
        // If setup is already done, skip to dashboard
        try {
            if (AppSetting::setupCompleted()) {
                $this->redirect(route('dashboard'), navigate: true);
            }
        } catch (\Throwable) {
            // Database not ready — stay on the setup wizard
        }
    }

    public function nextStep(): void
    {
        if ($this->step === 2 && empty(trim($this->catName))) {
            $this->addError('catName', 'Please enter your cat\'s name.');
            return;
        }

        if ($this->step < $this->totalSteps) {
            $this->step++;
            return;
        }

        // Final step — save everything
        $this->completeSetup();
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function useDefaults(): void
    {
        $this->tempWarning = 39.0;
        $this->tempCritical = 39.5;
        $this->bpmWarning = 220;
        $this->bpmCritical = 250;
    }

    protected function completeSetup(): void
    {
        // If the user chose to seed demo data, run the seeder first.
        // This populates the 7 demo cats, thresholds, alerts, and settings.
        if ($this->seedDemoData) {
            Artisan::call('db:seed', ['--force' => true]);

            // The seeder creates its own cats and settings, so we just need to
            // activate the chosen provider and mark setup complete.
            app(ProviderManager::class)->activate($this->provider);

            // Update thresholds to the user's choices (override seeded defaults)
            Threshold::updateOrCreate(
                ['cat_id' => null, 'vital' => 'temperature'],
                ['warning_value' => $this->tempWarning, 'critical_value' => $this->tempCritical],
            );
            Threshold::updateOrCreate(
                ['cat_id' => null, 'vital' => 'bpm'],
                ['warning_value' => $this->bpmWarning, 'critical_value' => $this->bpmCritical],
            );
        } else {
            // No demo data — create just the user's first cat
            Cat::create([
                'name' => trim($this->catName),
                'breed' => $this->catBreed,
                'birth_year' => $this->birthYear,
                'status' => 'healthy',
            ]);

            // Set global thresholds
            Threshold::updateOrCreate(
                ['cat_id' => null, 'vital' => 'temperature'],
                ['warning_value' => $this->tempWarning, 'critical_value' => $this->tempCritical],
            );
            Threshold::updateOrCreate(
                ['cat_id' => null, 'vital' => 'bpm'],
                ['warning_value' => $this->bpmWarning, 'critical_value' => $this->bpmCritical],
            );

            // Activate the chosen provider
            app(ProviderManager::class)->activate($this->provider);
        }

        // Mark setup complete
        AppSetting::markSetupComplete();

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.setup-wizard')
            ->layout('layouts.app');
    }
}
