<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
  protected $fillable = [
    'sale_return_id','sale_item_id','batch_id','quantity','unit_price','line_refund'
  ];

  public function saleReturn(){ return $this->belongsTo(SaleReturn::class); }
  public function saleItem(){ return $this->belongsTo(SaleItem::class); }
  public function batch(){ return $this->belongsTo(Batch::class); }
}
