<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Basegroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'basegroup_code',
        'basegroup_name',
        'delivery_percentage'
    ];

    public function subGroups()
    {
        return $this->hasMany(Subgroup::class, 'basegroup_id');
    }
}
