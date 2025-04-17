<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->boolean('can_activate_maintenance')->default(false);
            $table->boolean('can_switch_to_roadmap_maintenance')->default(false);
            $table->boolean('can_enable_announcement')->default(false);
            $table->boolean('can_enable_market_purchases')->default(false);
            $table->boolean('can_enable_discussion')->default(false);
            $table->boolean('can_enable_item_creation')->default(false);
            $table->boolean('can_enable_customization')->default(false);
            $table->boolean('can_enable_trading')->default(false);
            $table->boolean('can_enable_spaces')->default(false);
            $table->boolean('can_manage_users')->default(false);
            $table->boolean('can_manage_user_settings')->default(false);
            $table->boolean('can_manage_staff')->default(false);
            $table->boolean('can_manage_items')->default(false);
            $table->boolean('can_manage_spaces')->default(false);
            $table->boolean('can_verify_users')->default(false);
            $table->boolean('can_gift_users')->default(false);
            $table->boolean('can_enable_real_life_purchases')->default(false);
            $table->boolean('can_enable_registration')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_roles');
    }
};
