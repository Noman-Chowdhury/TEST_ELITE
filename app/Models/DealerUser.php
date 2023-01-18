<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealerUser extends Model
{
    use HasFactory;

    protected $fillable = [
      'level'
    ];

    public function scanPoints()
    {
        return $this->hasMany(ScanPoint::class,'dealer_id');
    }

    public function volumePoints()
    {
        return $this->hasMany(VolumeTransfer::class,'dealer_id')->where('status', '!=',2);
    }
    public function bonusPoints()
    {
        return $this->hasMany(BonusPoint::class,'dealer_id')->where('soft_delete', '=',1);
    }

    public function redeemPoints()
    {
        return $this->hasMany(RedeemPoint::class,'dealer_id');
    }
}
