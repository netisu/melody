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
        Schema::create('space_ranks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('space_id');
            $table->integer('rank');
            $table->string('name');

            // Existing permissions
            $table->boolean('can_make_games')->default(false);
            $table->boolean('can_view_wall')->default(false);
            $table->boolean('can_kick_users')->default(false);

            // New permissions
            $table->boolean('can_manage_members')->default(false); // Add/remove members
            $table->boolean('can_edit_space')->default(false);    // Change space settings
            $table->boolean('can_moderate_content')->default(false); // Delete posts, etc.
            $table->boolean('can_manage_ranks')->default(false); // Create/edit ranks
            $table->boolean('can_invite_users')->default(false);  // Invite new users

            $table->timestamps();

            $table->foreign('space_id')->references('id')->on('spaces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_ranks');
    }
};
