<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmartCollectionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_smart_collection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_collection_id')->constrained('tbl_smart_collections')->onDelete('cascade');
            $table->unsignedBigInteger('item_id');
            $table->string('item_type');
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
        Schema::dropIfExists('tbl_smart_collection_items');
    }
}
