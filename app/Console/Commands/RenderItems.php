<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Jobs\ItemRenderer;
use Illuminate\Console\Command;
use App\Jobs\ItemPreviewRenderer;

class RenderItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:render-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Do you wish to rerender ALL items?')) {
            $items = Item::all();
            foreach ($items as $item) {

                if ($item->item_type !== 'face' || $item->item_type !== 'head') {
                    ItemRenderer::dispatch($item->id)->onQueue('render');
                    ItemPreviewRenderer::dispatch($item->id, true, $item->hash)->onQueue('render');
                    sleep(3);
                }

                if ($item->item_type === 'face' || $item->item_type === 'head') {
                    ItemPreviewRenderer::dispatch($item->id, true, $item->hash)->onQueue('render');
                    sleep(3);
                }
            }
        }
    }
}
