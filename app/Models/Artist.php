<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    protected $table = 'tbl_artist';

    protected $guarded = array();

    protected $fillable = ['name','bio','address','image','status'];

    protected $casts = [
        'status' => 'integer',
    ];

    protected $appends = ['image_url'];

    /**
     * Get full image URL or default placeholder.
     */
    public function getImageUrlAttribute()
    {
        return $this->image
            ? url('storage/artist/' . $this->image)
            : asset('assets/imgs/no_img.png');
    }

    
}
