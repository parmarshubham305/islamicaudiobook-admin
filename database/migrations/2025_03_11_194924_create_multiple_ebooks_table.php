<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultipleEbooksTable extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_multiple_e_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ebook_id')->constrained('tbl_ebooks')->onDelete('cascade');
            $table->text('upload_file');
            $table->string('ebook_name', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_multiple_e_books');
    }
}