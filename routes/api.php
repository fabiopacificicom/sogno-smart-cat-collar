<?php

use App\Http\Controllers\SensorDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Internal API Routes
|--------------------------------------------------------------------------
| The desktop app exposes these endpoints. The ESP32 collar POSTs sensor
| data to /api/sensor-data via a tunnel (ngrok, Cloudflare Tunnel, etc.).
*/

Route::post('/sensor-data', [SensorDataController::class, 'store']);
