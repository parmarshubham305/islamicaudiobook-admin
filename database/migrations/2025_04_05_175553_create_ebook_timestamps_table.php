<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbookTimestampsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ebook_timestamps', function (Blueprint $table) {
            $table->id('timestamp_id'); // Custom primary key
            $table->unsignedBigInteger('ebook_id');
            $table->unsignedBigInteger('user_id');
            $table->string('timestamp'); // VARCHAR field
            $table->timestamps();

            // Optional: Add foreign keys
            // $table->foreign('ebook_id')->references('id')->on('ebooks')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ebook_timestamps');
    }
}
