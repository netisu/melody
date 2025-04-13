<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StopRenderer extends Command
{
    protected $signature = 'app:stop-renderer';
    protected $description = 'Stop the Renderer server';

    public function handle()
    {
        $port = 8001; // Replace with your actual port

        $command = "kill -9 \$(lsof -i TCP:$port | grep LISTEN | awk '{print \$2}')";

        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("Render server stopped successfully.");
            logger()->info('Render server stopped successfully.');
        } else {
            $this->error("Error stopping Render server:");
            $this->error(implode("\n", $output));
            logger()->error('Error stopping Render server: ' . implode("\n", $output));
        }
    }
}
