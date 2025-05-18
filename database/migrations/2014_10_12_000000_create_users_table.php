<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('display_name');
            $table->string('email')->unique();
            $table->string('birthdate')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('next_reward_payout')->nullable();
            $table->string('password');
            $table->integer('event_currency')->default(10);
            $table->integer('sparkles')->default(10);
            $table->integer('stars')->default(1);
            $table->string('status');
            $table->string('about_me');
            $table->string('social_id')->nullable();
            $table->string('social_type')->nullable();
            $table->string('wallet_address')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
