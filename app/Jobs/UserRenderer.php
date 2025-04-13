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

        $newVrcInstance = new RenderController();
        $vrs = $newVrcInstance;
        $vrs->UserRender($this->userID);
    }
}
