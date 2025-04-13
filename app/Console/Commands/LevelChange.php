<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LevelChange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:level-change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-aligns all user levels based on their total experience.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->confirm('Do you wish to re-align ALL user levels based on their total experience?')) {
            $users = User::all();

            foreach ($users as $user) {
                $this->info('Processing User Id: ' . $user->id);
                $this->realignLevel($user);
            }

            $this->info('All user levels have been re-aligned based on their total experience.');
        }
    }

    /**
     * Re-aligns the user's level based on their total experience from the experiences table.
     *
     * @param User $user
     * @return void
     */
    protected function realignLevel(User $user)
    {
        $totalExperience = DB::table('experiences')->where('user_id', $user->id)->sum('experience_points');
        $levels = DB::table('levels')->orderBy('level')->get();
        $newLevel = 1; // Default starting level
        $nextLevelExperience = null;

        foreach ($levels as $level) {
            if ($totalExperience >= $level->next_level_experience) {
                $newLevel = $level->level;
            } else {
                $nextLevelExperience = $level->next_level_experience;
                break; // Found the next level
            }
        }

        // Update the user's level and next level experience
        if ($user->level !== $newLevel) {
            $this->info("  - Updating User Id: {$user->id}  to Level {$newLevel} (Total Experience: {$totalExperience})");
            DB::table('experiences')->where('user_id', $user->id)->update(['level_id' => $newLevel, 'experience_points' => $nextLevelExperience]);
        } else {
            DB::table('experiences')->where('user_id', $user->id)->update(['experience_points' => $nextLevelExperience]);
        }
    }
}