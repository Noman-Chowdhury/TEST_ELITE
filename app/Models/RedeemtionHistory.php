<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RedeemtionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
      'from',
      'to',
      'earned',
      'redeem',
      'balance',
    ];
}
