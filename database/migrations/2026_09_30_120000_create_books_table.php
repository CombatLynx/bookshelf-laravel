<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create('books', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->unique();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
}
