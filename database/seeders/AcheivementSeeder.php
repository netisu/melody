<?php

use Illuminate\Database\Seeder;
use App\Models\Achievement; // Replace with your actual Achievement model

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $site = config('Values.name');
        $achievements = [
            [
                'name' => 'Administrator',
                'is_secret' => false,
                'description' => 'Players who possess this badge are official administrators. Administrators are members of our staff who oversee the website and keep it running smoothly.',
                'image' => 'assets/img/achievements/administrator.png',
            ],
            [
                'name' => 'Verified First Quest',
                'is_secret' => false,
                'description' => 'Successfully finish your first quest.',
                'image' => 'assets/img/achievements/first-quest.png',
            ],
            [
                'name' => 'Egg Hunt 2025',
                'is_secret' => false,
                'description' => 'Collect every character in the game.',
                'image' => 'assets/img/achievements/all-characters.png',
            ],
            [
                'name' => 'Daily Login Streak of 30',
                'is_secret' => false,
                'description' => "Log in to {$site} for 30 consecutive days.",
                'image' => 'assets/img/achievements/login-streak.png',
            ],
            [
                'name' => 'Defeat the Final Boss',
                'is_secret' => false,
                'description' => 'Conquer the ultimate challenge!',
                'image' => 'assets/img/achievements/final-boss.png',
            ],
            [
                'name' => 'Collect 1000 Coins',
                'is_secret' => false,
                'description' => 'Accumulate a fortune! (and please dont spend it all on a birkin bag)',
                'image' => 'assets/img/achievements/1000-coins.png',
            ],
            [
                'name' => 'enigmaticAcheivement',
                'is_secret' => true,
                'description' => '???.... Discover a hidden secret',
                'image' => 'assets/img/achievements/enigmatic-gem.png',
            ],
        ];

        // Insert all acheivements
        DB::table('acheivements')->insert($achievements);

    }
}