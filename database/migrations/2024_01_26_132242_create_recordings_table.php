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
            $table->longText('description')->nullable();
            $table->string('file');
            $table->string('thumbnail');
            $table->string('duration');
            $table->string('size');
            $table->string('type')->nullable();
            $table->string('status');
            $table->string('privacy')->nullable();
            $table->string('share')->nullable();
            $table->string('embed')->nullable();
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
