<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VolumeController extends Controller
{
    public function transferData()
    {
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");
        $data = DB::select(DB::raw("Select dealer_id,painter_id,code,code2, sum(quantity) as total_quantity, sum(dealer_point) as dealer_total_point,sum(painter_point) as painter_total_point, created_at from volume_tranfers group by code"));
        $int = collect($data)->map(function ($ttt) {
            return (array)$ttt;
        })->toArray();
        foreach (array_chunk($int,1000) as $red)
        {
            DB::table('volume_transfers')->insert($red);
        }
    }
}
