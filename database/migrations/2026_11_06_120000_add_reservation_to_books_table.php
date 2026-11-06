<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReservationToBooksTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedBigInteger('reservation_reader_id')->nullable();
            $table->string('reservation_reader_name')->nullable();
            $table->date('reservation_placed_on')->nullable();
            $table->date('reservation_expires_on')->nullable();
            $table->string('reservation_phase')->nullable();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn([
                'reservation_reader_id',
                'reservation_reader_name',
                'reservation_placed_on',
                'reservation_expires_on',
                'reservation_phase',
            ]);
        });
    }
}
