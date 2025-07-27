<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $table = 'tbl_package';
    protected $guarded = array();

    protected $fillable = ['name','price','currency_type', 'android_product_package','ios_product_package','type','time','status','image', 'identifier'];

    protected $casts = [
        'price' => 'integer',
        'status' => 'integer',
    ];

    public function entities()
    {
        return $this->morphedByMany(Audio::class, 'entity', 'subscription_entities')
            ->union($this->morphedByMany(Video::class, 'entity', 'subscription_entities'))
            ->union($this->morphedByMany(EBook::class, 'entity', 'subscription_entities'));
    }

    public function audios()
    {
        return $this->morphedByMany(Audio::class, 'entity', 'subscription_entities');
    }

    public function videos()
    {
        return $this->morphedByMany(Video::class, 'entity', 'subscription_entities');
    }

    public function ebooks()
    {
        return $this->morphedByMany(EBook::class, 'entity', 'subscription_entities');
    }

    public function getAllEntitiesAttribute()
    {
        return collect()
            ->merge($this->audios)
            ->merge($this->videos)
            ->merge($this->ebooks);
    }
}
