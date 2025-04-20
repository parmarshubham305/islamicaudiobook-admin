<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'tbl_user';

    protected $guarded = array();

    protected $fillable = ['user_name','full_name','email','password','date_of_birth','mobile_number','country_code','gender','image','type','bio','status','device_token','device_type'];

    protected $hidden = ['password',];

    protected $casts = [
        'gender' => 'integer',
        'type' => 'integer',
        'status' => 'integer',
    ];

    public function customPackages()
    {
        return $this->hasManyThrough(
            CustomPackage::class,         // Final model
            CustomTransaction::class,     // Intermediate model
            'user_id',                    // Foreign key on CustomTransaction table
            'id',                         // Foreign key on CustomPackage table
            'id',                         // Local key on User table
            'custom_package_id'           // Local key on CustomTransaction table
        );
    }
}
