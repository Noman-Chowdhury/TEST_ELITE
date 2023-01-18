<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redeemtion extends Model
{
    use HasFactory;

    protected $fillable = [
        'volumes',
        'total_scan_point',
        'bonus_point',
        'total_point',
        'complete_redeem_point',
        'dealer_id',
        'painter_id',
        'transaction_code',
        'transaction_date',
        'status',
        'start_date',
        'end_date',
        'code',
        'balance',
        'processing_redeem_point',
        'total_redeem_point'
    ];
}
