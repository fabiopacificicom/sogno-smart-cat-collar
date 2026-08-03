<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Base for the mobile sync API. Every endpoint except /pair requires a
 * valid device token (Authorization: Bearer <token>). Resolves the device,
 * rejects revoked/unknown tokens, and stamps last_seen_at.
 */
abstract class MobileApiController extends Controller
{
    protected function authenticateDevice(Request $request): Device|JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'Missing device token'], 401);
        }

        $device = Device::findByToken($token);

        if (! $device) {
            return response()->json(['error' => 'Invalid or revoked device token'], 401);
        }

        $device->touchLastSeen();

        return $device;
    }
}
