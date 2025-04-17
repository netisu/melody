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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->integer('creator_id')->nullable();
            $table->string('creator_type')->default('user');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('hash')->nullable();
            $table->string('item_type');
            $table->string('status')->default('review'); // review, moderated, accepted
            $table->boolean('public_view')->default(true);
            $table->integer('cost_coins');
            $table->integer('cost_bucks');
            $table->boolean('sale_ongoing')->default(false);
            $table->string('percent_off')->default(25);
            $table->string('avatar_preview')->nullable();
            $table->string('thumbnail_hash')->nullable();
            $table->boolean('rare')->default(false);
            $table->integer('in_event')->nullable();
            $table->integer('event_id')->nullable();
            $table->boolean('in_style')->default(false);
            $table->integer('style_of')->nullable();
            $table->boolean('onsale');
            $table->integer('initial_stock')->default(0);
            $table->integer('remaining_stock')->default(0);
            $table->integer('number_favorites')->default(0);
            $table->timestamp('time_off_sale')->nullable();
            $table->timestamp('time_on_sale')->nullable();
            $table->integer('impression_count')->unsigned()->default(0);
            $table->boolean('trade_lock')->default(false);
            $table->boolean('limit_copies')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
