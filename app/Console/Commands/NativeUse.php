<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Swap the active NativePHP config (and app ID) for a build target.
 *
 * NativePHP Electron (desktop) and NativePHP Mobile share a single
 * published config file — config/nativephp.php — but with different
 * schemas, and both read NATIVEPHP_APP_ID. Since one codebase powers
 * both platforms, we keep two config files and swap the active one:
 *
 *   config/nativephp.desktop.php  → desktop schema
 *   config/nativephp.mobile.php   → mobile schema
 *
 * This command copies the requested platform's config into place and
 * writes the matching NATIVEPHP_APP_ID into .env, so the subsequent
 * native:* command (native:serve/native:build for desktop,
 * native:run/native:package for mobile) picks up the right settings.
 */
class NativeUse extends Command
{
    protected $signature = 'native:use {platform : desktop|mobile}';

    protected $description = 'Activate the NativePHP config + app ID for a build target (desktop or mobile)';

    /**
     * App IDs per platform. Mobile uses a distinct ID so the two builds
     * don't collide in stores, updaters, or OS app registries.
     */
    protected array $appIds = [
        'desktop' => 'com.pacificdev.smartcatcollar',
        'mobile' => 'com.pacificdev.smartcatcollar.mobile',
    ];

    public function handle(): int
    {
        $platform = strtolower($this->argument('platform'));

        if (! in_array($platform, ['desktop', 'mobile'])) {
            $this->error('Invalid platform. Use "desktop" or "mobile".');

            return self::FAILURE;
        }

        $source = config_path("nativephp.{$platform}.php");
        $target = config_path('nativephp.php');

        if (! file_exists($source)) {
            $this->error("Missing {$source}. Run the appropriate native:install first.");

            return self::FAILURE;
        }

        copy($source, $target);
        $this->setEnvValue('NATIVEPHP_APP_ID', $this->appIds[$platform]);

        $this->info("✅ NativePHP target set to [{$platform}]");
        $this->line("   config/nativephp.php ← nativephp.{$platform}.php");
        $this->line("   NATIVEPHP_APP_ID={$this->appIds[$platform]}");

        return self::SUCCESS;
    }

    /**
     * Write (or update) an env key in .env.
     */
    protected function setEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');
        $contents = file_exists($envPath) ? file_get_contents($envPath) : '';

        if (preg_match("/^{$key}=/m", $contents)) {
            $contents = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $contents);
        } else {
            $contents = rtrim($contents).PHP_EOL."{$key}={$value}".PHP_EOL;
        }

        file_put_contents($envPath, $contents);
    }
}
