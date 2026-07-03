<?php

declare(strict_types=1);

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IdempotencyCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'idempotency:cleanup
                            {--hours= : Number of hours to retain idempotency keys (default: from config)}
                            {--dry-run : Preview records to be deleted without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired idempotency keys older than the TTL';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) ($this->option('hours') ?? config('foundation.idempotency.ttl_hours', 24));
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subHours($hours);

        $query = DB::table('idempotency_keys')
            ->where('created_at', '<', $cutoff);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No idempotency keys older than ' . $hours . ' hour(s) found.');

            return 0;
        }

        if ($dryRun) {
            $this->info("[Dry Run] Would delete {$count} idempotency key(s) older than {$hours} hour(s).");

            return 0;
        }

        $deleted = $query->delete();

        $this->info("Deleted {$deleted} idempotency key(s) older than {$hours} hour(s).");

        return 0;
    }
}
