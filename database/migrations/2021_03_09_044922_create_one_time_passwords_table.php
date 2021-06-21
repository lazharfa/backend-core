<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOneTimePasswordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('one_time_passwords', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 10);
            $table->string('otp_hash');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('code', 30);
            $table->string('contact');
            $table->string('action');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('one_time_passwords');
    }
}
