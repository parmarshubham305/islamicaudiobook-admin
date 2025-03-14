<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timestemp extends Model
{
    use HasFactory;

    protected $table = 'tbl_timestemp';

    protected $guarded = array();

    protected $fillable = ['audio_id','user_id','timestemp','is_audiobook'];

    
}
