<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Market\MarketController;
/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('store:refresh', function () {
    // 1. Fetch new data from your source (API, database, etc.)
    $mc = new MarketController;
    $newData = $mc->getNewData(); 

    // 2. Update your database or perform other refresh actions
    // ... your logic to process and store $newData ...

    $this->info('Store data refreshed successfully!');
})->purpose('Refresh store data every 24 hours'); 

// ... your private function getNewData() ...
