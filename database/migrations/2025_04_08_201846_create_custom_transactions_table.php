<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('tbl_custom_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('tbl_user')->onDelete('cascade');
            $table->foreignId('custom_package_id')->constrained('tbl_custom_packages')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_id');
            $table->string('currency_code');
            $table->date('expiry_date');
            $table->boolean('status')->default(true);
            $table->timestamps(); // auto manages created_at and updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('tbl_custom_transactions');
    }
}
