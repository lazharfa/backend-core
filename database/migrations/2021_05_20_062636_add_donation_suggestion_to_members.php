<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDonationSuggestionToMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('minimum_donation')->default(10000);
            $table->text('donation_suggestion')
                ->default('[{"amount":50000,"notes":null},{"amount":100000,"notes":null},{"amount":200000,"notes":null},{"amount":500000,"notes":null}]');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('members', function (Blueprint $table) {
            //
        });
    }
}
