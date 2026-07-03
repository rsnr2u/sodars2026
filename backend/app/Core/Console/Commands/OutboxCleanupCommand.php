<?php

declare(strict_types=1);

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OutboxCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outbox:cleanup
                            {--days= : Number of days to retain processed records (default: from config)}
                            {--dry-run : Preview records to be deleted without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove processed outbox events older than the retention period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('foundation.outbox.cleanup_days', 30));
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $query = DB::table('outbox_events')
            ->where('status', 'processed')
            ->where('processed_at', '<', $cutoff);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No processed outbox events older than ' . $days . ' day(s) found.');

            return 0;
        }

        if ($dryRun) {
            $this->info("[Dry Run] Would delete {$count} processed outbox event(s) older than {$days} day(s).");

            return 0;
        }

        $deleted = $query->delete();

        $this->info("Deleted {$deleted} processed outbox event(s) older than {$days} day(s).");

        return 0;
    }
}
