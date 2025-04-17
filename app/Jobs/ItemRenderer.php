<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Endpoints\RenderController;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ItemRenderer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $itemID;

    /**
     * Create a new job instance.
     */
    public function __construct($itemID)
    {
        $this->itemID = $itemID;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->itemID))->releaseAfter(60)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $newVrcInstance = new RenderController();
        $vrs = $newVrcInstance;
        $vrs->ItemRender($this->itemID);
    }
}
