<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $guarded =[];


    public function users()
{
    return $this->hasMany(\App\Models\User::class);
}

protected static function booted()
{
    static::creating(function ($shop) {
        if (empty($shop->qr_token)) {
            $shop->qr_token = \Illuminate\Support\Str::random(60);
        }
    });
}
}
