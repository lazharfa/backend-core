<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQurbanOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qurban_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable(true);
            $table->foreign('parent_id')->references('id')->on('qurban_orders');
            $table->integer('donor_id');
            $table->foreign('donor_id')->references('id')->on('users');
            $table->integer('donation_id');
            $table->foreign('donation_id')->references('id')->on('donations');
            $table->integer('qurban_type_id');
            $table->foreign('qurban_type_id')->references('id')->on('qurban_types');
            $table->integer('qurban_location_id');
            $table->foreign('qurban_location_id')->references('id')->on('qurban_locations');
            $table->string('qurban_name');
            $table->string('qurban_status');
            $table->double('qurban_price');
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
        Schema::dropIfExists('qurban_orders');
    }
}
