<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A mobile device paired with this desktop hub for the read-only sync API.
 *
 * Pairing flow: desktop Settings generates a single-use pairing code (stored
 * hashed in app_settings, short TTL). The phone POSTs the code to
 * /api/mobile/pair; on success we create a Device with a long-lived token
 * (stored here as a SHA-256 hash — the plaintext is returned to the phone
 * exactly once). Subsequent sync calls authenticate with the token.
 */
class Device extends Model
{
    protected $fillable = [
        'name',
        'token_hash',
        'paired_at',
        'last_seen_at',
        'revoked_at',
    ];

    protected $casts = [
        'paired_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Generate a new API token and return it plaintext (show once).
     * The model stores only the hash.
     */
    public static function issueToken(): array
    {
        $plaintext = 'scc_'.Str::random(48);

        return [$plaintext, hash('sha256', $plaintext)];
    }

    public static function hashToken(string $plaintext): string
    {
        return hash('sha256', $plaintext);
    }

    /**
     * Find an active (non-revoked) device by its plaintext token.
     */
    public static function findByToken(string $plaintext): ?self
    {
        return static::where('token_hash', static::hashToken($plaintext))
            ->whereNull('revoked_at')
            ->first();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }

    public function touchLastSeen(): void
    {
        $this->forceFill(['last_seen_at' => now()])->save();
    }
}
