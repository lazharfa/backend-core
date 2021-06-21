<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumQurbanOrderWhatsappJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whatsapp_jobs', function (Blueprint $table) {
            $table->integer('qurban_order_id')->nullable();
            $table->foreign('qurban_order_id')->references('id')->on('qurban_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('whatsapp_jobs', function (Blueprint $table) {
            $table->dropColumn('qurban_order_id');
            $table->dropForeign('whatsapp_jobs_whatsapp_messages_id_fk');
        });
    }
}
