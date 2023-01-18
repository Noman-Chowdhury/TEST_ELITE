<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
      'user_class',
      'user_id',
      'date',
      'month',
      'year',
      'redeem_point',
      'bonus_point',
      'scan_point',
      'volume_point',
      'earned_point',
      'balance',
    ];
}
