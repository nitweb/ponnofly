<?php

namespace App\Console\Commands;

use App\Models\AdminActivity;
use Illuminate\Console\Command;

class PruneAdminActivities extends Command
{
    protected $signature = 'admin:prune-activities';

    protected $description = 'Delete admin activity records older than the retention period';

    public function handle(): int
    {
        $deleted = AdminActivity::query()
            ->where('created_at', '<', now()->subDays(AdminActivity::RETENTION_DAYS))
            ->delete();

        $this->info("Deleted {$deleted} old admin activity record(s).");

        return self::SUCCESS;
    }
}
