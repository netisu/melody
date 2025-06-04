<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Endpoints\RenderController;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class UserRenderer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userID;



    /**
     * Create a new job instance.
     */
    public function __construct($userID)
    {
        $this->userID = $userID;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->userID))->releaseAfter(60)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info('--- UserRenderer job started for user ID: ' . $this->userID . ' ---');

        try {
            $newVrcInstance = new RenderController();
            $vrs = $newVrcInstance;
            \Log::info('Calling RenderController::UserRender() with user ID: ' . $this->userID);
            $vrs->UserRender($this->userID);
            \Log::info('RenderController::UserRender() finished for user ID: ' . $this->userID);

        } catch (\Exception $e) {
            \Log::error('*** Error in UserRenderer job for user ID ' . $this->userID . ' ***: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }
}
