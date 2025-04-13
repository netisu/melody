<?php

use Illuminate\Support\Facades\DB;
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
        $sitename = config("Values.name");
        $randomMessages = [
            "We guarantee it\'s more fun than watching paint dry (unless you\'re into that)",
            "Get ready to unleash your inner productivity beast!",
            "Psst... there might be a surprise waiting for you ",
            "Let\'s make today awesome!",
            "We\'ve got new features to explore! Check them out.",
            "Don\'t forget to register today!",
        ];

        Schema::create('site_configuration', function (Blueprint $table) use ($sitename, $randomMessages) {
            $table->id();
            $table->boolean('site_maintenance')->default(true);
            $table->boolean('site_roadmap_maintenance')->default(false);
            $table->boolean('announcement_enabled')->default(true);
            $table->string('announcement_message')->default("Welcome to {$sitename}! " . $randomMessages[array_rand($randomMessages)])->nullable();
            $table->boolean('market_purchases_enabled')->default(true);
            $table->boolean('market_enabled')->default(true);
            $table->boolean('discussion_enabled')->default(true);
            $table->boolean('verification_program_enabled')->default(false);
            $table->boolean('item_creation_enabled')->default(true);
            $table->boolean('customization_enabled')->default(true);
            $table->boolean('trading_enabled')->default(true);
            $table->boolean('spaces_enabled')->default(true);
            $table->boolean('user_settings_enabled')->default(true);
            $table->boolean('real_life_purchases_enabled')->default(true);
            $table->boolean('registration_enabled')->default(true);
            $table->timestamps();
        });

        DB::table('site_configuration')->insert(['id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_flags');
    }
};
