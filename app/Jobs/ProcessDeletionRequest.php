<?php

namespace App\Jobs;

use App\Models\DeletionRequest;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDeletionRequest implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public DeletionRequest $request)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->request->update(['status' => 'processing']);

        $user = User::where('email', $this->request->email)->first();

        if (!$user) {
            $this->request->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);
            return;
        }

        $user->delete();

        $this->request->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }
}
