<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_id','description','created_by','Debit','Credit','batch_id', ];

    public function vendor()
    {
        return $this->belongsTo(vendor::class);
    }

    public function creator() {
    return $this->belongsTo(User::class, 'created_by');

    }
     public function batch()
    {
        return $this->belongsTo(AccessoryBatch::class, 'batch_id');
    }
}
