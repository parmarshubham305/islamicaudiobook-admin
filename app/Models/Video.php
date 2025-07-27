<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $table = 'tbl_video';

    protected $guarded = array();

    protected $fillable = ['name','category_id','subcategory_id','user_id','artist_id','video_type','url','download','description','is_feature','is_paid','v_view','image','is_approved','is_created_by_admin'];

    protected $casts = [
        'is_feature' => 'integer',
        'category_id' => 'integer',
        'subcategory_id' => 'integer',
        'artist_id' => 'integer',

    ];

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

    public function smartCollections() {
        return $this->morphToMany(SmartCollection::class, 'item', 'tbl_smart_collection_items');
    }
}
