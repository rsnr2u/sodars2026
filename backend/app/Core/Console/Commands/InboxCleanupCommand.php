<?php

declare(strict_types=1);

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InboxCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inbox:cleanup
                            {--days=30 : Number of days to retain processed inbox records}
                            {--dry-run : Preview records to be deleted without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove processed inbox events older than the retention period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subDays($days);

        $query = DB::table('inbox_events')
            ->where('processed_at', '<', $cutoff);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No inbox events older than ' . $days . ' day(s) found.');

            return 0;
        }

        if ($dryRun) {
            $this->info("[Dry Run] Would delete {$count} inbox event(s) older than {$days} day(s).");

            return 0;
        }

        $deleted = $query->delete();

        $this->info("Deleted {$deleted} inbox event(s) older than {$days} day(s).");

        return 0;
    }
}
