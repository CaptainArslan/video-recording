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
            $table->longText('file');
            $table->longText('thumbnail');
            $table->string('duration')->nullable();
            $table->string('size')->nullable();
            $table->string('type')->nullable();
            $table->tinyInteger('status')->default(1);
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
