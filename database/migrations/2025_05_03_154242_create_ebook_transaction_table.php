<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbookTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ebook_transaction', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('ebook_id');
            $table->string('amount');
            $table->string('payment_id');
            $table->string('currency_code');
            $table->integer('status')->default(1);
            $table->integer('is_purchased')->default(0);
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
        Schema::dropIfExists('tbl_ebook_transaction');
    }
}
