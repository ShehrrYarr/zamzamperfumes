<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTransfer extends Model
{
    use HasFactory;
    protected $guarded =[];

    public function items() { return $this->hasMany(\App\Models\BatchTransferItem::class); }
public function fromShop() { return $this->belongsTo(\App\Models\Shop::class, 'from_shop_id'); }
public function toShop() { return $this->belongsTo(\App\Models\Shop::class, 'to_shop_id'); }
}
