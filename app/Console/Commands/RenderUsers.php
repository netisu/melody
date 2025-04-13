<?php

namespace App\Console\Commands;

use App\Jobs\UserRenderer;
use Illuminate\Console\Command;
use App\Models\Avatar;
use App\Models\User;

class RenderUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:render-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-renders all users.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Do you wish to re-render ALL users?')) {
            $avatars = Avatar::where('image', '!=', 'default')->get();

            foreach ($avatars as $avatar) {
                $user = User::find($avatar->user_id); // Check if the user exists

                if ($user) {
                    // Only proceed if the user exists
                    $this->info('Rendering User Id: ' . $avatar->user_id);
                    UserRenderer::dispatch($avatar->user_id)->onQueue('user-render');
                    sleep(3);
                } else {
                    $this->error("Avatar Record For ID {$avatar->user_id} Missing, Skipping...");
                }
            }
        }
    }
}
