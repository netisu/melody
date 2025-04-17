<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() : void
    {
        $baseExperience = 250;
        $multiplier = 2.5; //used to be 0.3 4/9/2025: used to be 1.3, upped to 1.7, 4/9/2025: upped to 2.5
        $levels = [];

        // Add level 1 with null next_level_experience
        $levels[] = [
            'level' => 1,
            'next_level_experience' => null,
        ];

        // Loop through levels starting from 2 (skip level 1)
        for ($level = 2; $level <= 300; $level++) {
            $experience = round($baseExperience + ($multiplier * ($level - 1)));
            $baseExperience = $experience;

            $levels[] = [
                'level' => $level,
                'next_level_experience' => $experience,
            ];
        }

        // Insert all levels in a single batch for efficiency
        DB::table('levels')->insert($levels);
    }
}
