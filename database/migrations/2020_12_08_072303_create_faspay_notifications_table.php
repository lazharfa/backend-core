<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaspayNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('faspay_notifications')) {
            Schema::create('faspay_notifications', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('code', 50);
                $table->string('status', 10);
                $table->text('responses');
                $table->integer('donation_id')->nullable();
                $table->foreign('donation_id')->references('id')->on('donations');
                $table->timestamps();
            });
        }   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('faspay_notifications');
    }
}
