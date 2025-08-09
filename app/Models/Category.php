<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'tbl_category';

    protected $guarded = array();

    protected $fillable = ['name','image','status'];

    protected $casts = [
        'status' => 'integer',
    ];

    protected $appends = ['image_url'];

    public const IMAGE_FOLDER = 'category';

    public function subcategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }

    /**
     * Get full image URL or default placeholder.
     */
    public function getImageUrlAttribute()
    {
        return $this->image
            ? url('storage/category/' . $this->image)
            : asset('assets/imgs/no_img.png');
    }
}
