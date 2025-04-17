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
            $table->bigInteger('user_id')->unsigned();

            $table->string('image')->default('default');
            $table->integer('hat_1')->nullable();
            $table->integer('hat_2')->nullable();
            $table->integer('hat_3')->nullable();
            $table->integer('hat_4')->nullable();
            $table->integer('hat_5')->nullable();
            $table->integer('hat_6')->nullable();
            
            $table->integer('addon')->nullable();
            $table->integer('head')->nullable();
            $table->integer('face')->nullable();
            $table->integer('tool')->nullable();
            $table->integer('tshirt')->nullable();
            $table->integer('shirt')->nullable();
            $table->integer('pants')->nullable();

            $table->string('color_head')->default('d3d3d3');
            $table->string('color_torso')->default('055e96');
            $table->string('color_left_arm')->default('d3d3d3');
            $table->string('color_right_arm')->default('d3d3d3');
            $table->string('color_left_leg')->default('d3d3d3');
            $table->string('color_right_leg')->default('d3d3d3');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
