<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\ForumTopic;

class ForumTopicsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $site = config('Values.name');

        $topic = [
            [
                'name' => "{$site}",
                'description' => 'Important news such as new features, ideas to talk about, events and server events will be posted here.',
                'section_id' => 1,
                'hidden' => false,
                'is_staff_only_viewing' => false,
                'role_required_to_post' => 2,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
                        [
                'name' => "Updates",
                'description' => 'Important updates to the website will be posted here.',
                'section_id' => 1,
                'hidden' => false,
                'is_staff_only_viewing' => false,
                'role_required_to_post' => 2,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
            [
                'name' => 'Admin Discussion',
                'description' => 'This is the place for admins to communicate with eachother.',
                'section_id' => 1,
                'hidden' => false,
                'is_staff_only_viewing' => true,
                'role_required_to_post' => 5,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
            [
                'name' => "General",
                'description' => "Talk with other users on {$site} at one place",
                'section_id' => 2,
                'hidden' => false,
                'is_staff_only_viewing' => false,
                'role_required_to_post' => null,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
            [
                'name' => 'Off-Topic',
                'description' => 'If there\'s no other subforum that suits the content you want to post, post it here!',
                'section_id' => 2,
                'hidden' => false,
                'is_staff_only_viewing' => false,
                'role_required_to_post' => null,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
            [
                'name' => 'Marketplace',
                'description' => 'Are you interested in advertising or selling your items? This is the place for you!',
                'section_id' => 2,
                'hidden' => false,
                'is_staff_only_viewing' => false,
                'role_required_to_post' => null,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
            [
                'name' => 'Creative',
                'description' => "Do you have a creative work of art? Post it here.",
                'section_id' => 2,
                'hidden' => false,
                'is_staff_only_viewing' => false,
                'role_required_to_post' => null,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
            [
                'name' => 'Vent',
                'description' => 'This topic is for getting things off your chest.',
                'section_id' => 3,
                'hidden' => false,
                'is_staff_only_viewing' => false,
                'role_required_to_post' => null,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
            [
                'name' => 'Support',
                'description' => 'Having issues or bugs? Post them here.',
                'section_id' => 3,
                'hidden' => false,
                'is_staff_only_viewing' => false,
                'role_required_to_post' => null,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
            [
                'name' => 'Reports',
                'description' => 'Any wrongdoers? Report them here or use the report function.',
                'section_id' => 3,
                'hidden' => false,
                'is_staff_only_viewing' => false,
                'role_required_to_post' => null,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ],
            [
                'name' => 'The Underground',
                'description' => 'Secrets, conspiracies, and forbidden knowledge.',
                'section_id' => 3,
                'hidden' => true,
                'is_staff_only_viewing' => true,
                'role_required_to_post' => null,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ]
        ];

        foreach ($topic as $TopicData) {
            ForumTopic::create($TopicData);
        }
    }
}
