<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAvatarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_avatars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            $table->string('image')->default('default');
            $table->json('hat_1')->nullable();
            $table->json('hat_2')->nullable();
            $table->json('hat_3')->nullable();
            $table->json('hat_4')->nullable();
            $table->json('hat_5')->nullable();
            $table->json('hat_6')->nullable();

            $table->json('addon')->nullable();
            $table->json('head')->nullable();
            $table->json('face')->nullable();
            $table->json('tool')->nullable();
            $table->json('tshirt')->nullable();
            $table->json('shirt')->nullable();
            $table->json('pants')->nullable();

            $table->json('colors')->nullable();
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
        Schema::dropIfExists('user_avatars');
    }
}
