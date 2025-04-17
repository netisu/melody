<?php
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForumTopicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up(): void
    {
        $site = config('Values.name');

        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->unsignedBigInteger('section_id');
            $table->boolean('hidden')->default(false);
            $table->boolean('is_staff_only_viewing')->default(false);
            $table->unsignedBigInteger('role_required_to_post')->default(1)->nullable();
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('forum_sections')->onDelete('cascade');
            $table->foreign('role_required_to_post')->references('id')->on('admin_roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_topics');
    }
}
