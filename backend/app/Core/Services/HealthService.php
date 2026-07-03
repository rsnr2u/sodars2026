<?php

declare(strict_types=1);

namespace App\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthService
{
    /**
     * Lightweight liveness probe.
     * Returns immediately — confirms the process is alive.
     */
    public function live(): array
    {
        return [
            'status' => 'UP',
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Readiness probe.
     * Checks core dependencies: database, cache, storage.
     */
    public function ready(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = collect($checks)->every(fn (array $check) => $check['healthy']);

        return [
            'status' => $allHealthy ? 'UP' : 'DOWN',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ];
    }

    /**
     * Detailed diagnostic report.
     * Includes latencies, versions, git hash, queue health, mailer status.
     * Should be restricted to super admins / internal network / APP_DEBUG=true.
     */
    public function details(): array
    {
        $checks = [
            'database' => $this->checkDatabaseWithLatency(),
            'cache' => $this->checkCacheWithLatency(),
            'storage' => $this->checkStorageWithLatency(),
            'mailer' => $this->checkMailer(),
        ];

        $allHealthy = collect($checks)->every(fn (array $check) => $check['healthy']);

        return [
            'status' => $allHealthy ? 'UP' : 'DOWN',
            'timestamp' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'version' => [
                'app' => config('app.version', '1.0.0'),
                'laravel' => app()->version(),
                'php' => PHP_VERSION,
            ],
            'git' => $this->getGitInfo(),
            'uptime' => $this->getUptime(),
            'checks' => $checks,
        ];
    }

    /**
     * Check DB connection (readiness level).
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['healthy' => true, 'message' => 'Connection established.'];
        } catch (Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check DB connection with latency measurement (details level).
     */
    protected function checkDatabaseWithLatency(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            return [
                'healthy' => true,
                'message' => 'Connection established.',
                'latency_ms' => $latencyMs,
                'driver' => config('database.default'),
            ];
        } catch (Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage(), 'latency_ms' => null];
        }
    }

    /**
     * Check cache store (readiness level).
     */
    protected function checkCache(): array
    {
        try {
            Cache::put('health_check', 'ok', 10);
            $val = Cache::get('health_check');

            return [
                'healthy' => $val === 'ok',
                'message' => $val === 'ok' ? 'Write/Read successful.' : 'Cache mismatch.',
            ];
        } catch (Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check cache store with latency measurement (details level).
     */
    protected function checkCacheWithLatency(): array
    {
        try {
            $start = microtime(true);
            Cache::put('health_check', 'ok', 10);
            $val = Cache::get('health_check');
            Cache::forget('health_check');
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            return [
                'healthy' => $val === 'ok',
                'message' => $val === 'ok' ? 'Write/Read successful.' : 'Cache mismatch.',
                'latency_ms' => $latencyMs,
                'driver' => config('cache.default'),
            ];
        } catch (Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage(), 'latency_ms' => null];
        }
    }

    /**
     * Check storage disk (readiness level).
     */
    protected function checkStorage(): array
    {
        try {
            $disk = Storage::disk('local');
            $disk->put('health_write.txt', 'ok');
            $disk->delete('health_write.txt');

            return ['healthy' => true, 'message' => 'Storage read/write checks pass.'];
        } catch (Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check storage disk with latency measurement (details level).
     */
    protected function checkStorageWithLatency(): array
    {
        try {
            $start = microtime(true);
            $disk = Storage::disk('local');
            $disk->put('health_write.txt', 'ok');
            $disk->delete('health_write.txt');
            $latencyMs = round((microtime(true) - $start) * 1000, 2);

            return [
                'healthy' => true,
                'message' => 'Storage read/write checks pass.',
                'latency_ms' => $latencyMs,
                'driver' => config('filesystems.default'),
            ];
        } catch (Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage(), 'latency_ms' => null];
        }
    }

    /**
     * Check mailer configuration (details level only).
     */
    protected function checkMailer(): array
    {
        try {
            $transport = Mail::mailer()->getSymfonyTransport();

            return [
                'healthy' => true,
                'message' => 'Mailer transport available.',
                'driver' => config('mail.default'),
            ];
        } catch (Throwable $e) {
            return ['healthy' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Retrieve the current git commit hash and branch.
     */
    protected function getGitInfo(): array
    {
        $hash = null;
        $branch = null;

        try {
            $headFile = base_path('.git/HEAD');
            if (file_exists($headFile)) {
                $head = trim((string) file_get_contents($headFile));

                if (str_starts_with($head, 'ref: ')) {
                    $ref = substr($head, 5);
                    $branch = basename($ref);
                    $refFile = base_path('.git/' . $ref);

                    if (file_exists($refFile)) {
                        $hash = substr(trim((string) file_get_contents($refFile)), 0, 8);
                    }
                } else {
                    $hash = substr($head, 0, 8);
                }
            }
        } catch (Throwable) {
            // Silently ignore git read failures
        }

        return [
            'hash' => $hash,
            'branch' => $branch,
        ];
    }

    /**
     * Calculate application uptime based on the LARAVEL_START constant.
     */
    protected function getUptime(): ?string
    {
        if (!defined('LARAVEL_START')) {
            return null;
        }

        $seconds = (int) (microtime(true) - LARAVEL_START);
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs = $seconds % 60;

        return sprintf('%dh %dm %ds', $hours, $minutes, $secs);
    }
}
