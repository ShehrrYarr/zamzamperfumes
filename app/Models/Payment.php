<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'shop_id','sale_id','method','bank_id','amount','paid_at'
    ];

    public function sale(){ return $this->belongsTo(\App\Models\Sale::class); }
    public function shop(){ return $this->belongsTo(\App\Models\Shop::class); }
    public function bank(){ return $this->belongsTo(\App\Models\Bank::class); }
}
