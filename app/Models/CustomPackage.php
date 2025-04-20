<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomPackage extends Model
{
    protected $table = 'tbl_custom_packages';

    protected $fillable = [
        'name',
        'price',
        'image',
        'currency_type',
        'android_product_package',
        'ios_product_package',
        'time',
        'type',
        'status',
    ];

    protected $appends = ['is_buy', 'image_url'];

    public function getIsBuyAttribute()
    {
        // Expire outdated transactions (optional, can be moved elsewhere)
        CustomTransaction::where('status', 1)
            ->whereDate('expiry_date', '<', now())
            ->update(['status' => 0]);

        // Check if this user has an active transaction for this package
        $activeTransaction = CustomTransaction::where('custom_package_id', $this->id)
            ->where('status', 1)
            ->first();

        return $activeTransaction ? 1 : 0;
    }

    /**
     * Get full image URL or default placeholder.
     */
    public function getImageUrlAttribute()
    {
        return $this->image
            ? url('public/storage/custom-package/' . $this->image)
            : asset('assets/imgs/no_img.png');
    }

    // One-to-many relation with the pivot model
    public function customPackageSmartCollections()
    {
        return $this->hasMany(CustomPackageSmartCollection::class, 'custom_package_id');
    }

    // Many-to-many relation with smart collections
    public function smartCollections()
    {
        return $this->belongsToMany(SmartCollection::class, 'tbl_custom_package_smart_collections', 'custom_package_id', 'smart_collection_id');
    }
}