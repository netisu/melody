<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('verified')->default(false)->index();
            $table->boolean('verified_email')->default(false);
            $table->boolean('beta_tester')->default(false)->index();
            $table->boolean('private_profile')->default(false)->index();
            $table->boolean('followers_enabled')->default(true)->index(); // Index added to followers_enabled
            $table->boolean('trading_enabled')->default(true)->index(); // Index added to trading_enabled
            $table->boolean('posting_enabled')->default(true)->index(); // Index added to posting_enabled
            $table->boolean('chat_enabled')->default(true)->index(); // Index added to inbox_enabled
            $table->boolean('notifications_enabled')->default(true)->index(); // Index added to notifications_enabled
            $table->boolean('profile_picture_enabled')->default(false)->index();
            $table->string('profile_picture_url')->default('none');
            $table->boolean('headshot_enabled')->default(true)->index();
            $table->boolean('alert_enabled')->default(false)->index();
            $table->string('alert_message')->nullable();
            $table->boolean('calling_card_enabled')->default(false)->index();
            $table->string('calling_card_url')->default('none');
            $table->boolean('profile_banner_enabled')->default(false)->index();
            $table->string('profile_banner_url')->default('none');
            $table->integer('primary_space')->nullable()->index();
            $table->integer('secondary_space')->nullable()->index();
            $table->boolean('ugc_creator')->default(false)->index();
            $table->boolean('content_creator')->default(false)->index();
            $table->string('theme')->default('light');
            $table->string('country_code')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
