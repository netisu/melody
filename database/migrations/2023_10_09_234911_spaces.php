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
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('owner_id')->unsigned();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('verified')->default(false);
            $table->boolean('private')->default(false);
            $table->boolean('alert')->default(false);
            $table->text('alert_message')->nullable();
            $table->boolean('vault_viewable')->default(true);
            $table->string('primary_color')->default('none');
            $table->boolean('locked')->default(false);
            $table->string('thumbnail');
            $table->boolean('thumbnail_pending')->default(true);
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
