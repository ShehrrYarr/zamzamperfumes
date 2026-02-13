<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
     protected $fillable = [
        'shop_id','user_id','expense_date','category','title','notes','amount'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function shop(){ return $this->belongsTo(Shop::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
