<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoanToBooksTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('borrower_name')->nullable();
            $table->date('borrowed_on')->nullable();
            $table->date('due_on')->nullable();
            $table->unsignedTinyInteger('renewals')->default(0);
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['borrower_name', 'borrowed_on', 'due_on', 'renewals']);
        });
    }
}
