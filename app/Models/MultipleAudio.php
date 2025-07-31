<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultipleAudio extends Model
{
    protected $table = 'tbl_multiple_audio'; // Table name explicitly defined

    protected $primaryKey = 'id';

    public $timestamps = false; // Disable timestamps if not using created_at and updated_at

    protected $fillable = [
        'audio_id',
        'upload_file',
        'isAudioTab',
        'audio_name',
    ];

    protected $appends = ['file_url']; // 👈 Append the custom attribute

    /**
     * Accessor for file_url.
     */
    public function getFileUrlAttribute()
    {
        return $this->upload_file
            ? url('audio/' . $this->upload_file)
            : null;
    }
}
