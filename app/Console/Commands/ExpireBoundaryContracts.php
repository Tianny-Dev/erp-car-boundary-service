<?php

namespace App\Console\Commands;

use App\Models\BoundaryContract;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireBoundaryContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire all active boundary contracts that have passed their end date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredStatusId = Status::where('name', 'expired')->value('id');

        if (!$expiredStatusId) {
            $this->error('Status "expired" not found in the database.');
            return self::FAILURE;
        }

        $affected = BoundaryContract::whereHas('status', fn($q) => $q->where('name', 'active'))
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', Carbon::today())
            ->update(['status_id' => $expiredStatusId]);

        $this->info("Expired {$affected} boundary contract(s).");

        return self::SUCCESS;
    }
}
