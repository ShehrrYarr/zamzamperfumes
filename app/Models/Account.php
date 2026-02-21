<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $fillable = [
        'shop_id', 'name', 'code', 'is_active',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function entries()
    {
        return $this->hasMany(AccountEntry::class);
    }

    // Computed balance (credit - debit)
    public function getBalanceAttribute(): float
    {
        $credit = (float)$this->entries()->sum('credit');
        $debit  = (float)$this->entries()->sum('debit');
        return round($credit - $debit, 2);
    }
}
