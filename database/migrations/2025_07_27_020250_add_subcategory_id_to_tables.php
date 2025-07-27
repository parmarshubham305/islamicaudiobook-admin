<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubcategoryIdToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_video', function (Blueprint $table) {
            $table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');
        });

        Schema::table('tbl_audio', function (Blueprint $table) {
            $table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');
        });

        Schema::table('tbl_ebooks', function (Blueprint $table) {
            $table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_video', function (Blueprint $table) {
            $table->dropColumn('subcategory_id');
        });

        Schema::table('tbl_audio', function (Blueprint $table) {
            $table->dropColumn('subcategory_id');
        });

        Schema::table('tbl_ebooks', function (Blueprint $table) {
            $table->dropColumn('subcategory_id');
        });
    }
}
