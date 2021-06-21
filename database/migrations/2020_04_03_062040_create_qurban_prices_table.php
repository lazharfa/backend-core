<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQurbanPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qurban_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('qurban_type_id');
            $table->foreign('qurban_type_id')->references('id')->on('qurban_types');
            $table->integer('qurban_location_id');
            $table->foreign('qurban_location_id')->references('id')->on('qurban_locations');
            $table->double('price');
            $table->timestamps();
            $table->softDeletes('deleted_at', 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qurban_prices');
    }
}
