<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchTransferItem extends Model
{
    use HasFactory;
    protected $guarded =[];

    public function transfer() { return $this->belongsTo(\App\Models\BatchTransfer::class, 'batch_transfer_id'); }
public function batch() { return $this->belongsTo(\App\Models\Batch::class); }

}
