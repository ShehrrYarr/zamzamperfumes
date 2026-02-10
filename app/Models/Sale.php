<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
     protected $fillable = [
        'shop_id','user_id',
        'customer_name','customer_phone',
        'subtotal','discount_type','discount_value','discount_amount','grand_total',
        'status'
    ];

    public function items(){ return $this->hasMany(\App\Models\SaleItem::class); }
    public function payments(){ return $this->hasMany(\App\Models\Payment::class); }
    public function shop(){ return $this->belongsTo(\App\Models\Shop::class); }
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}
