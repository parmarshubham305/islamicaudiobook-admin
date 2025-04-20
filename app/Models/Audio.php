<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    use HasFactory;

    protected $table = 'tbl_audio';

    protected $guarded = array();

    protected $fillable = ['name','category_id','user_id','publisher_id','artist_id','video_type','url','download','description','is_feature','is_paid','v_view','image','audio','is_aiaudiobook','price','is_approved','isAudioTab','audio_content','is_created_by_admin','upload_file'];

    protected $casts = [
        'is_feature' => 'integer',
        'category_id' => 'integer',
        'artist_id' => 'integer',

    ];

    protected $appends = ['image_url', 'file_url', 'multiple_audio_files'];

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class,'artist_id');
    }

    public function multipleAudioFiles()
    {
        return $this->hasMany(MultipleAudio::class, 'audio_id');
    }

    /**
     * Get full image URL or default placeholder.
     */
    public function getImageUrlAttribute()
    {
        return $this->image
            ? url('public/storage/audio/' . $this->image)
            : asset('assets/imgs/default-image.png');
    }

    /**
     * Get full file URL or default placeholder.
     */
    public function getFileUrlAttribute()
    {
        return $this->upload_file
            ? url('public/storage/audio/' . $this->upload_file)
            : null;
    }

    public function getMultipleAudioFilesAttribute()
    {
        return $this->multipleAudioFiles()->get();
    }
}
