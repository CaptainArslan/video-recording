<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            // $table->string('user_name')->nullable()->unique();
            $table->string('email')->unique();
            // $table->string('phone')->nullable()->unique();
            $table->string('role')->default(1)->comment('0 => admin , 1 => company , 2 => user');
            $table->string('location_id')->nullable();
            $table->string('ghl_api_key')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('added_by')->default(1);
            $table->string('image')->nullable();
            $table->string('status')->default(1);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
