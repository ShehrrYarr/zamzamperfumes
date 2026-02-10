<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
    
     protected $fillable = [
        'shop_id','name','account_title','account_number','iban','is_active'
    ];

    public function shop()
    {
        return $this->belongsTo(\App\Models\Shop::class);
    }
}
