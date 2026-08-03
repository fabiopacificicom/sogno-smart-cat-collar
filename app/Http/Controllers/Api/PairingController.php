<?php

namespace App\Http\Controllers\Api;

use App\Models\AppSetting;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /api/mobile/pair — exchange a single-use pairing code (shown in
 * desktop Settings → Mobile Devices) for a long-lived device token.
 *
 * The pairing code is stored hashed in app_settings with a short TTL and is
 * consumed on first successful use. The returned token is shown to the phone
 * exactly once; we store only its hash on the Device row.
 */
class PairingController extends MobileApiController
{
    public const CODE_TTL_MINUTES = 10;

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'name' => 'nullable|string|max:100',
        ]);

        if (! $this->codeIsValid($validated['code'])) {
            return response()->json(['error' => 'Invalid or expired pairing code'], 422);
        }

        // Consume the code (single-use) before issuing the device.
        $this->clearCode();

        [$plaintext, $hash] = Device::issueToken();

        $device = Device::create([
            'name' => $validated['name'] ?: 'Mobile device',
            'token_hash' => $hash,
            'paired_at' => now(),
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'status' => 'paired',
            'device_id' => $device->id,
            'name' => $device->name,
            'token' => $plaintext,
        ], 201);
    }

    /**
     * Generate a fresh single-use pairing code (called from Settings UI).
     * Returns the plaintext code to display; stores only the hash + expiry.
     */
    public static function generateCode(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        AppSetting::set('mobile_pairing_code_hash', hash('sha256', $code));
        AppSetting::set('mobile_pairing_code_expires_at', now()->addMinutes(self::CODE_TTL_MINUTES)->toIso8601String());

        return $code;
    }

    protected function codeIsValid(string $code): bool
    {
        $hash = AppSetting::get('mobile_pairing_code_hash');
        $expiresAt = AppSetting::get('mobile_pairing_code_expires_at');

        if (! $hash || ! $expiresAt) {
            return false;
        }

        if (now()->isAfter($expiresAt)) {
            return false;
        }

        return hash_equals($hash, hash('sha256', trim($code)));
    }

    protected function clearCode(): void
    {
        AppSetting::set('mobile_pairing_code_hash', '');
        AppSetting::set('mobile_pairing_code_expires_at', '');
    }
}
