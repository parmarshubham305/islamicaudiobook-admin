<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_custom_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->text('image')->nullable();
            $table->string('currency_type')->default('USD');
            $table->string('android_product_package')->nullable();
            $table->string('ios_product_package')->nullable();
            $table->string('time'); // e.g. 1, 6
            $table->string('type'); // e.g. Month, Year
            $table->boolean('status')->default(true);
            $table->timestamps(); // auto manages created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_custom_packages');
    }
}
