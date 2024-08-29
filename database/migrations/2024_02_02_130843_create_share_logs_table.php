<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShareLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('share_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recording_id')->constrained('recordings')->onDelete('cascade');
            $table->string('contact_id')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('type');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('all_tags')->nullable();
            $table->string('status');
            $table->string('conversation_id')->nullable();
            $table->text('message')->unullable();
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
        Schema::dropIfExists('share_logs');
    }
}
