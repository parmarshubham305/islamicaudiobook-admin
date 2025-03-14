<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbooksTable extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_ebooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('artist_id');
            $table->unsignedBigInteger('publisher_id')->default(1);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->string('file_type')->nullable();
            $table->string('url')->nullable();
            $table->integer('is_feature')->default(0);
            $table->text('description');
            $table->enum('is_paid', ['0', '1'])->default('0');
            $table->integer('v_view')->default(0);
            $table->text('ebook_file')->nullable();
            $table->text('image')->nullable();
            $table->tinyInteger('is_approved')->default(0);
            $table->integer('is_created_by_admin')->default(0);
            $table->integer('price')->nullable();
            $table->text('ebook_content')->nullable();
            $table->text('upload_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_ebooks');
    }
}