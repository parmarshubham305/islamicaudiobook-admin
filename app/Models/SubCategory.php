<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubCategory extends Model
{
    use HasFactory;

    protected $table = 'tbl_sub_categories';

    protected $fillable = ['category_id', 'name', 'image', 'status'];

    protected $casts = [
        'status' => 'integer',
    ];

    protected $appends = ['image_url'];

    public const IMAGE_FOLDER = 'category';

    // Relation to Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        return url('storage/' . self::IMAGE_FOLDER . '/' . $this->image);
    }

    // Helper to delete an image file
    public function deleteImageFile($filename = null)
    {
        $imageName = $filename ?? $this->image;

        if ($imageName) {
            $imagePath = self::IMAGE_FOLDER . '/' . $imageName;

            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }
    }

    // Boot method to register model events
    protected static function boot()
    {
        parent::boot();

        // Delete old image on record deletion
        static::deleting(function ($subcategory) {
            $subcategory->deleteImageFile();
        });

        // Delete old image if replaced during update
        static::updating(function ($subcategory) {
            if ($subcategory->isDirty('image')) {
                $originalImage = $subcategory->getOriginal('image');
                $subcategory->deleteImageFile($originalImage);
            }
        });
    }
}