<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomPackageSmartCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_custom_package_smart_collections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('custom_package_id')
                  ->constrained('tbl_custom_packages')
                  ->onDelete('cascade');

            $table->foreignId('smart_collection_id')
                  ->constrained('tbl_smart_collections')
                  ->onDelete('cascade');

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
        Schema::dropIfExists('tbl_custom_package_smart_collections');
    }
}
