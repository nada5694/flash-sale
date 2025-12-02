<?php

namespace App\Jobs;

use App\Models\Hold;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireHoldsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $affected = Hold::where('used', false)
            ->where('expires_at', '<=', now())
            ->update(['used' => true]);

        if ($affected > 0) {
            Log::info('ExpireHoldsJob: expired holds marked as used.', [
                'count' => $affected,
            ]);
        }
    }
}
