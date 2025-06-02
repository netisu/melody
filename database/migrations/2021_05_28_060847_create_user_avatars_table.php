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
            $table->json('hat_1')->nullable()->change();
            $table->json('hat_2')->nullable()->change();
            $table->json('hat_3')->nullable()->change();
            $table->json('hat_4')->nullable()->change();
            $table->json('hat_5')->nullable()->change();
            $table->json('hat_6')->nullable()->change();

            $table->json('addon')->nullable()->change();
            $table->json('head')->nullable()->change();
            $table->json('face')->nullable()->change();
            $table->json('tool')->nullable()->change();
            $table->json('tshirt')->nullable()->change();
            $table->json('shirt')->nullable()->change();
            $table->json('pants')->nullable()->change();

            $table->json('colors')->nullable()->after('pants');
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
