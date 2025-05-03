<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EBook_Transaction extends Model
{
    use HasFactory;

    protected $table = 'tbl_ebook_transaction';
    protected $guarded = array();

    protected $fillable = ['user_id','ebook_id','amount','payment_id','currency_code','status', 'is_purchased'];

    protected $casts = [
        'user_id' => 'integer',
        'ebook_id' => 'integer',
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function ebook()
    {
        return $this->belongsTo(EBook::class, 'ebook_id');
    }
}
