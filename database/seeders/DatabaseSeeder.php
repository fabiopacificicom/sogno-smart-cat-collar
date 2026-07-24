<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\AppSetting;
use App\Models\Cat;
use App\Models\ProviderSetting;
use App\Models\SensorReading;
use App\Models\Threshold;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the real 7 cats and demo data.
     */
    public function run(): void
    {
        // --- Global default thresholds ---
        Threshold::create(['cat_id' => null, 'vital' => 'temperature', 'warning_value' => 39.0, 'critical_value' => 39.5]);
        Threshold::create(['cat_id' => null, 'vital' => 'bpm', 'warning_value' => 220, 'critical_value' => 250]);

        // --- The 7 real cats (from HANDOFF.md) ---
        $cats = [
            ['name' => 'Antifa',  'breed' => 'Domestic Shorthair', 'status' => 'healthy',  'temp' => 38.4, 'bpm' => 175, 'activity' => 'high'],
            ['name' => 'Anakin',  'breed' => 'Domestic Shorthair', 'status' => 'healthy',  'temp' => 38.7, 'bpm' => 190, 'activity' => 'medium'],
            ['name' => 'Mando',   'breed' => 'Domestic Shorthair', 'status' => 'warning',  'temp' => 39.1, 'bpm' => 225, 'activity' => 'low'],
            ['name' => 'Grogu',   'breed' => 'Domestic Shorthair', 'status' => 'critical', 'temp' => 39.8, 'bpm' => 260, 'activity' => 'low'],
            ['name' => 'Gaza',    'breed' => 'Domestic Shorthair', 'status' => 'healthy',  'temp' => 38.2, 'bpm' => 160, 'activity' => 'high'],
            ['name' => 'Jabba',   'breed' => 'Domestic Shorthair', 'status' => 'healthy',  'temp' => 38.9, 'bpm' => 185, 'activity' => 'medium'],
            ['name' => 'Sabbia',  'breed' => 'Domestic Shorthair', 'status' => 'healthy',  'temp' => 38.6, 'bpm' => 170, 'activity' => 'medium'],
        ];

        foreach ($cats as $data) {
            $cat = Cat::create([
                'name' => $data['name'],
                'breed' => $data['breed'],
                'birth_year' => now()->year - rand(1, 8),
                'status' => $data['status'],
            ]);

            // Latest reading matching the HANDOFF values
            SensorReading::create([
                'cat_id' => $cat->id,
                'temperature' => $data['temp'],
                'bpm' => $data['bpm'],
                'activity' => $data['activity'],
                'source' => 'mock',
                'read_at' => now()->subMinutes(rand(1, 5)),
            ]);

            // Generate 15 historical readings per cat (last ~7 hours).
            // NOTE: Do not use Model::factory() here — fakerphp/faker is a
            // require-dev dependency and is NOT bundled in the desktop build,
            // so $this->faker would be null in production (causing a 500 error
            // on the setup wizard's "Load demo data" path). Generate the
            // values with plain PHP instead.
            for ($i = 1; $i <= 15; $i++) {
                SensorReading::create([
                    'cat_id' => $cat->id,
                    'temperature' => round(mt_rand(378, 389) / 10, 1), // 37.8 - 38.9
                    'bpm' => mt_rand(120, 210),
                    'activity' => ['medium', 'high'][array_rand(['medium', 'high'])],
                    'source' => 'mock',
                    'read_at' => now()->subMinutes($i * 30),
                ]);
            }
        }

        // --- Seed alerts matching the prototype ---
        $grogu = Cat::where('name', 'Grogu')->first();
        $mando = Cat::where('name', 'Mando')->first();
        $gaza = Cat::where('name', 'Gaza')->first();

        Alert::create([
            'cat_id' => $grogu->id,
            'type' => 'critical',
            'vital' => 'temperature',
            'value' => '39.8°C',
            'threshold' => 39.5,
            'message' => 'Temperature 39.8°C exceeds critical threshold (39.5°C)',
        ]);

        Alert::create([
            'cat_id' => $mando->id,
            'type' => 'warning',
            'vital' => 'bpm',
            'value' => '225',
            'threshold' => 220,
            'message' => 'BPM 225 exceeds warning threshold (220)',
        ]);

        Alert::create([
            'cat_id' => $grogu->id,
            'type' => 'critical',
            'vital' => 'bpm',
            'value' => '260',
            'threshold' => 250,
            'message' => 'BPM 260 exceeds critical threshold (250)',
        ]);

        Alert::create([
            'cat_id' => $mando->id,
            'type' => 'warning',
            'vital' => 'temperature',
            'value' => '39.1°C',
            'threshold' => 39.0,
            'message' => 'Temperature 39.1°C exceeds warning threshold (39.0°C)',
        ]);

        Alert::create([
            'cat_id' => $gaza->id,
            'type' => 'info',
            'vital' => 'activity',
            'value' => 'high',
            'threshold' => null,
            'message' => 'Activity level returned to normal',
        ]);

        // --- App settings ---
        AppSetting::set('setup_completed', '1');
        AppSetting::set('theme', 'system');
        AppSetting::set('language', 'en');
        AppSetting::set('auto_start', '0');
        AppSetting::set('start_minimized', '0');
        AppSetting::set('auto_download_updates', '1');
        AppSetting::set('notification_sound', '1');

        // --- Provider settings: mock is active by default ---
        ProviderSetting::activate('mock');
    }
}
