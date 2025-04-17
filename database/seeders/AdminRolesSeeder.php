<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminRoles;

class AdminRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $roles = [
            [
                'name' => 'Founder',
                'description' => 'Team members with this role are the founders of the website.',
                'can_activate_maintenance' => true,
                'can_switch_to_roadmap_maintenance' => true,
                'can_enable_announcement' => true,
                'can_enable_market_purchases' => true,
                'can_enable_discussion' => true,
                'can_enable_item_creation' => true,
                'can_enable_customization' => true,
                'can_enable_trading' => true,
                'can_enable_spaces' => true,
                'can_manage_users' => true,
                'can_manage_user_settings' => true,
                'can_manage_staff' => true,
                'can_manage_items' => true,
                'can_manage_spaces' => true,
                'can_verify_users' => true,
                'can_gift_users' => true,
                'can_enable_real_life_purchases' => true,
                'can_enable_registration' => true,
            ],
            [
                'name' => 'Head Administrator',
                'description' => 'Team members with this role are the founders of the website.',
                'can_activate_maintenance' => false,
                'can_switch_to_roadmap_maintenance' => false,
                'can_enable_announcement' => true,
                'can_enable_market_purchases' => true,
                'can_enable_discussion' => true,
                'can_enable_item_creation' => true,
                'can_enable_customization' => true,
                'can_enable_trading' => true,
                'can_enable_spaces' => true,
                'can_manage_users' => true,
                'can_manage_user_settings' => true,
                'can_manage_staff' => false,
                'can_manage_items' => true,
                'can_manage_spaces' => true,
                'can_verify_users' => true,
                'can_gift_users' => true,
                'can_enable_real_life_purchases' => true,
                'can_enable_registration' => false,
            ],
            [
                'name' => 'Administrator',
                'description' => 'Team members with this role are the founders of the website.',
                'can_activate_maintenance' => false,
                'can_switch_to_roadmap_maintenance' => false,
                'can_enable_announcement' => false,
                'can_enable_market_purchases' => true,
                'can_enable_discussion' => true,
                'can_enable_item_creation' => true,
                'can_enable_customization' => true,
                'can_enable_trading' => true,
                'can_enable_spaces' => true,
                'can_manage_users' => true,
                'can_manage_user_settings' => true,
                'can_manage_staff' => false,
                'can_manage_items' => true,
                'can_manage_spaces' => true,
                'can_verify_users' => true,
                'can_gift_users' => true,
                'can_enable_real_life_purchases' => true,
                'can_enable_registration' => false,
            ],
            [
                'name' => 'Moderator',
                'description' => 'Team members with this role are the founders of the website.',
                'can_activate_maintenance' => false,
                'can_switch_to_roadmap_maintenance' => false,
                'can_enable_announcement' => false,
                'can_enable_market_purchases' => false,
                'can_enable_discussion' => false,
                'can_enable_item_creation' => false,
                'can_enable_customization' => false,
                'can_enable_trading' => false,
                'can_enable_spaces' => false,
                'can_manage_users' => true,
                'can_manage_user_settings' => true,
                'can_manage_staff' => false,
                'can_manage_items' => false,
                'can_manage_spaces' => false,
                'can_verify_users' => false,
                'can_gift_users' => false,
                'can_enable_real_life_purchases' => false,
                'can_enable_registration' => false,
            ],
            [
                'name' => 'Asset Developer',
                'description' => 'Team members with this role create items for our marketplace.',
                'can_activate_maintenance' => false,
                'can_switch_to_roadmap_maintenance' => false,
                'can_enable_announcement' => false,
                'can_enable_market_purchases' => false,
                'can_enable_discussion' => false,
                'can_enable_item_creation' => false,
                'can_enable_customization' => false,
                'can_enable_trading' => false,
                'can_enable_spaces' => false,
                'can_manage_users' => false,
                'can_manage_user_settings' => false,
                'can_manage_staff' => false,
                'can_manage_items' => true,
                'can_manage_spaces' => false,
                'can_verify_users' => false,
                'can_gift_users' => false,
                'can_enable_real_life_purchases' => false,
                'can_enable_registration' => false,
            ],
        ];

        foreach ($roles as $roleData) {
            AdminRoles::create($roleData);
        }
    }
}
