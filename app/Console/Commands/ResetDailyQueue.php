<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Queue;
use Carbon\Carbon;

class ResetDailyQueue extends Command
{
    protected $signature = 'queue:resetdaily';
    protected $description = 'Resets the queue every day at midnight';

    public function handle()
{
    \App\Models\Queue::query()->where('status', 'waiting')->update(['status' => 'pending_reset']);
    \App\Models\Queue::query()->where('status', 'serving')->update(['status' => 'waiting']);

    $this->info('Queue has been reset for the new day.');
}

}
