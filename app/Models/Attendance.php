<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
     protected $fillable = [
        'shop_id','user_id','work_date','check_in_at','check_out_at',
        'worked_minutes','daily_salary_snapshot','hourly_salary_snapshot',
        'earned_amount','status'
    ];

    protected $casts = [
        'work_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function shop(){ return $this->belongsTo(Shop::class); }
    public function user(){ return $this->belongsTo(User::class); }

    public function sessions()
{
    return $this->hasMany(AttendanceSession::class);
}
}
