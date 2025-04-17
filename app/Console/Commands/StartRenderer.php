<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class StartRenderer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:start-renderer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the Server for the Renderer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $StartServer = "cd /var/www/renderer && go run main.go > /dev/null 2>&1 &";

        exec($StartServer, $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("Render server started successfully.");
            logger()->info('Render server started successfully.');
        } else {
            $this->error("Error starting Render server:");
            $this->error(implode("\n", $output));
            logger()->error('Error starting Render server: ' . implode("\n", $output));
        }
    }
}
