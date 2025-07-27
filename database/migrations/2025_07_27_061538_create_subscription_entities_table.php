<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionEntitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_entities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->morphs('entity'); // This creates entity_id and entity_type
            $table->timestamps();

            $table->foreign('package_id')->references('id')->on('tbl_package')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscription_entities');
    }
}
