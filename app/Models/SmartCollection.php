<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmartCollection extends Model
{
    protected $table = 'tbl_smart_collections';

    protected $fillable = [
        'title',
        'description',
        'type',
        'image',
        'currency_type',
        'price',
        'status',
    ];

    protected $appends = ['image_url'];

    public function items()
    {
        return $this->hasMany(SmartCollectionItem::class, 'smart_collection_id');
    }

    /**
     * Get full image URL or default placeholder.
     */
    public function getImageUrlAttribute()
    {
        return $this->image
            ? url('public/storage/smart-collection/' . $this->image)
            : asset('assets/imgs/default-image.png');
    }

    public function customPackages()
    {
        return $this->belongsToMany(CustomPackage::class, 'tbl_custom_package_smart_collections', 'smart_collection_id', 'custom_package_id')
                    ->using(CustomPackageSmartCollection::class);
    }
}
