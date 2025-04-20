<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPackageSmartCollection extends Model
{
    use HasFactory;

    protected $table = 'tbl_custom_package_smart_collections';

    protected $fillable = [
        'custom_package_id',
        'smart_collection_id',
    ];

    // Relationships
    public function customPackage()
    {
        return $this->belongsTo(CustomPackage::class, 'custom_package_id');
    }

    public function smartCollection()
    {
        return $this->belongsTo(SmartCollection::class, 'smart_collection_id');
    }
}
