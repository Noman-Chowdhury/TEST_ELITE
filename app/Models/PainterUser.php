<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PainterUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'process_finish',
        'member_type_point',
        'membership',
    ];

    public function scanPoints()
    {
        return $this->hasMany(ScanPoint::class, 'painter_id');
    }

    public function volumePoints()
    {
        return $this->hasMany(VolumeTransfer::class, 'painter_id')->where('status', '!=', 2);
    }

    public function bonusPoints()
    {
        return $this->hasMany(BonusPoint::class, 'painter_id')->where('soft_delete', '=', 1);
    }

    public function redeemPoints()
    {
        return $this->hasMany(RedeemPoint::class, 'painter_id');
    }
}
