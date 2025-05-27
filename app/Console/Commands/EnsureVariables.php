<?php

namespace App\Console\Commands;

use App\Jobs\UserRenderer;
use Illuminate\Console\Command;
use App\Models\Avatar;
use App\Models\User;
use App\Models\UserSettings;

class EnsureVariables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ensure-variables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensures that accounts have the Avatar and UserSettings entry on the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Are you sure?')) {
            $users = User::all();

            foreach ($users as $user) {
                if ($user) {
                    // UserSettings Creation
                    if (!UserSettings::where(['user_id' => $user->id])->exists()) {
                        UserSettings::create([
                            'user_id' => $user->id,
                        ]);
                        $this->info("UserSettings created for User ID: " . $user->id);
                    } else {
                        $this->info("UserSettings already exists for User ID: " . $user->id);
                    }

                    // Beta Tester Update (if applicable)
                    if (config('app.env') !== 'local' && config('Values.in_testing_phase')) {
                        $userSettings = UserSettings::where('user_id', $user->id)->first(); // Get the settings

                        if ($userSettings && $userSettings->beta_tester !== true) {
                            $userSettings->update(['beta_tester' => true]);
                            $this->info("Beta tester enabled for User ID: " . $user->id);
                        } else {
                            $this->info("Beta tester already enabled or not applicable for User ID: " . $user->id);
                        }
                    }

                    // Avatar Creation
                    if (!Avatar::where(['user_id' => $user->id])->exists()) {
                        Avatar::create([
                            'user_id' => $user->id,
                        ]);
                        $this->info("Avatar created for User ID: " . $user->id);
                        $this->info('Rendering User Id: ' . $user->id); // Use $user->id
                        UserRenderer::dispatch($user->id)->onQueue('user-render'); // Use $user->id
                        sleep(3);
                    } else {
                        $this->info("Avatar already exists for User ID: " . $user->id);
                    }
                } else {
                    $this->error("User Record Missing, Skipping..."); // Changed error message.
                }
            }
        }
    }
}
