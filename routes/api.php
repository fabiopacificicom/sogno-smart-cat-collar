<?php

use App\Http\Controllers\Api\MobileSyncController;
use App\Http\Controllers\Api\PairingController;
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

/*
|--------------------------------------------------------------------------
| Mobile Sync API (read-only)
|--------------------------------------------------------------------------
| Paired mobile devices pull delta syncs of the desktop hub's data. The
| phone pairs once with a single-use code (Settings → Mobile Devices),
| then authenticates each call with its device token. See
| .specs/plans/feature-mobile-sync.md.
*/

Route::post('/mobile/pair', [PairingController::class, 'store']);
Route::get('/mobile/cats', [MobileSyncController::class, 'cats']);
Route::get('/mobile/readings', [MobileSyncController::class, 'readings']);
Route::get('/mobile/alerts', [MobileSyncController::class, 'alerts']);
Route::get('/mobile/thresholds', [MobileSyncController::class, 'thresholds']);
