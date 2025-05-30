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
        Schema::create('item_edit_styles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('item_id')->unsigned();
            $table->string('name')->unique(); // Name of the style (e.g., "Black")
            $table->string('description')->nullable();
            $table->string('hash')->nullable();
            $table->boolean('is_model')->default(false); // Indicates if this style applies to a 3D model
            $table->boolean('is_texture')->default(false); // Indicates if this style applies to a texture
            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_edit_styles');
    }
};
