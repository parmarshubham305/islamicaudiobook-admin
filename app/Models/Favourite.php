<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    use HasFactory;

    protected $table = 'tbl_favourite';
    protected $guarded = array();

    public function video()
    {
        return $this->belongsTo(Video::class,'video_id');
    }

    protected $casts = ['id','user_id' ,'type', 'video_id','status'];
}
