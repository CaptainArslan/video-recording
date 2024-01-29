<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecordingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('title');
            $table->string('description');
            $table->string('file');
            $table->string('thumbnail');
            $table->string('duration');
            $table->string('size');
            $table->string('type');
            $table->string('status');
            $table->string('visibility')->nullable();
            $table->string('privacy')->nullable();
            $table->string('password')->nullable();
            $table->string('download')->nullable();
            $table->string('share')->nullable();
            $table->string('embed')->nullable();
            $table->string('player')->nullable();
            $table->string('views')->nullable();
            $table->string('likes')->nullable();
            $table->string('dislikes')->nullable();
            $table->string('comments')->nullable();
            $table->string('comment')->nullable();
            $table->string('comment_vote')->nullable();
            $table->string('comment_reply')->nullable();
            $table->string('comment_reply_vote')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recordings');
    }
}
