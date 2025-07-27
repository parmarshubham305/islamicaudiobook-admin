<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EBook extends Model
{
    use HasFactory;

    protected $table = 'tbl_ebooks';
    protected $fillable = [
        'name',
        'artist_id',
        'publisher_id',
        'user_id',
        'category_id',
        'subcategory_id',
        'file_type',
        'url',
        'is_feature',
        'description',
        'is_paid',
        'v_view',
        'ebook_file',
        'image',
        'is_approved',
        'is_created_by_admin',
        'price',
        'ebook_content',
        'upload_file'
    ];

    protected $casts = [
        'is_feature' => 'integer',
        'category_id' => 'integer',
        'artist_id' => 'integer',

    ];

    protected $appends = ['image_url', 'file_url'];

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class,'subcategory_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class,'artist_id');
    }

    public function multipleEbooks()
    {
        return $this->hasMany(MultipleEbook::class, 'ebook_id');
    }

    /**
     * Get full image URL or default placeholder.
     */
    public function getImageUrlAttribute()
    {
        return $this->image
            ? url('public/storage/e-books/' . $this->image)
            : asset('assets/imgs/default-image.png');
    }

    /**
     * Get full file URL or default placeholder.
     */
    public function getFileUrlAttribute()
    {
        return $this->upload_file
            ? url('public/storage/e-books/' . $this->upload_file)
            : null;
    }

    public function subscriptions()
    {
        return $this->morphToMany(Package::class, 'entity', 'subscription_entities');
    }
}
