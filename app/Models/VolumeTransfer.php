<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolumeTransfer extends Model
{
    use HasFactory;

    protected $table = 'volume_tranfers';

    protected $fillable = [
        'basegroup_id',
        'is_complete'
    ];
}
