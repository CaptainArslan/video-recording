<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('contact_id')->nullable();
            $table->string('location_id')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('dnd')->nullable();
            $table->string('type')->nullable();
            $table->string('source')->nullable();
            $table->string('assigned_to')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('address1')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('business_id')->nullable();
            $table->json('tags')->nullable();
            $table->json('followers')->nullable();
            $table->string('country')->nullable();
            $table->json('additional_emails')->nullable();
            $table->json('attributions')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('date_added')->nullable();
            $table->string('date_updated')->nullable();
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
        Schema::dropIfExists('contacts');
    }
}
