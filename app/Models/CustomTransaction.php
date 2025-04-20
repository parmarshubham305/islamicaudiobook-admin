<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomTransaction extends Model
{
    use HasFactory;

    protected $table = 'tbl_custom_transactions';

    protected $fillable = [
        'user_id',
        'custom_package_id',
        'description',
        'amount',
        'payment_id',
        'currency_code',
        'expiry_date',
        'status',
    ];

    // Relation to User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation to Custom Package
    public function customPackage()
    {
        return $this->belongsTo(CustomPackage::class, 'custom_package_id');
    }
}
