<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
  protected $fillable = [
    'shop_id','sale_id','user_id','refund_amount','method','bank_id'
  ];

  public function items(){ return $this->hasMany(SaleReturnItem::class); }
  public function sale(){ return $this->belongsTo(Sale::class); }
  public function shop(){ return $this->belongsTo(Shop::class); }
  public function user(){ return $this->belongsTo(User::class); }
  public function bank(){ return $this->belongsTo(Bank::class); }
}
