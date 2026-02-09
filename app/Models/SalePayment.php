<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
     use HasFactory;

    protected $fillable = [
        'sale_id', 'method', 'bank_id', 'amount', 'reference_no', 'notes', 'processed_by', 'paid_at'
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
