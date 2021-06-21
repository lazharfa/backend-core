<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnVideoAvailableQurbanOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qurban_orders', function (Blueprint $table) {
            $table->dateTime('video_available_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qurban_orders', function (Blueprint $table) {
            $table->dropColumn('video_available_at');
        });
    }
}
