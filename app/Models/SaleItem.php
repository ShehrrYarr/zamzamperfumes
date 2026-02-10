<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;
     protected $fillable = [
        'sale_id','batch_id','barcode','item_name','unit_price','quantity','line_total'
    ];

    public function sale(){ return $this->belongsTo(\App\Models\Sale::class); }
    public function batch(){ return $this->belongsTo(\App\Models\Batch::class); }
}
