<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnQurbanLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qurban_locations', function (Blueprint $table) {
            $table->string('location_status')->default('Show');
            $table->string('location_cover', 100)->nullable();
            $table->integer('location_quota')->nullable();
            $table->text('location_description')->nullable();
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
        Schema::table('qurban_locations', function (Blueprint $table) {
            $table->dropColumn('location_status');
            $table->dropColumn( 'location_cover');
            $table->dropColumn('location_quota');
            $table->dropColumn('location_description');
            $table->dropSoftDeletes();
        });
    }
}
