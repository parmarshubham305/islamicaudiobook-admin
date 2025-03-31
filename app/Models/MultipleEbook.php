<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultipleEbook extends Model
{
    use HasFactory;

    protected $table = 'tbl_multiple_e_books';

    protected $fillable = [
        'ebook_id',
        'upload_file',
        'ebook_name',
    ];

    public function ebook() {
        return $this->belongsTo(Ebook::class, 'ebook_id');
    }

    // Accessor for upload_file to get public URL
    public function getUploadFileFullPathAttribute()
    {
        return public_path('e-book/' . $this->upload_file);
    }
}
