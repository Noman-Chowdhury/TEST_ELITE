<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedeemController extends Controller
{
    public function startRedeem()
    {
        $code = 'ABCE2323D';
        $start_date = '2022-09-01';
        $end_date = '2022-09-30';
        $date = Carbon::parse($start_date)->format('Y/m/d');
        $query =
            "SELECT a.painter_id,(a.volumes) volumes,SUM(a.scan_point) total_scan_point,SUM(a.bonus_point) bonus_point,
 (SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point)) as redeem_point,
 (SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point)) as total_point, '$code' as transaction_code, '$start_date' as start_date, '$end_date' as end_date
FROM (
SELECT VT.painter_id,sum(VT.painter_point) volumes,0 scan_point,0 bonus_point,0 paid,0 un_paid
FROM volume_tranfers VT
WHERE  DATE_FORMAT(VT.created_at, '%Y-%m-%d') <= '$end_date'
AND DATE_FORMAT(VT.created_at, '%Y-%m-%d') >= '$start_date'
AND VT.status !=2
GROUP BY VT.painter_id
UNION all
SELECT SP.painter_id, 0 volumes,sum(SP.point) scan_point,0 bonus_point,0 paid,0 un_paid
FROM scan_points SP
WHERE  DATE_FORMAT(SP.created_at, '%Y-%m-%d')  <= '$end_date'
AND DATE_FORMAT(SP.created_at, '%Y-%m-%d') >= '$start_date'
GROUP BY SP.painter_id
UNION all
SELECT BP.painter_id,0 volumes,0 scan_point,sum(BP.bonus_point) bonus_point,0 paid,0 un_paid
FROM bonus_points BP
WHERE DATE_FORMAT(BP.created_at, '%Y-%m-%d')  <= '$end_date'
AND DATE_FORMAT(BP.created_at, '%Y-%m-%d') >= '$start_date'
AND BP.soft_delete=1
GROUP BY BP.painter_id
UNION all
SELECT RM.painter_id ,0 volumes,0 scan_point,0 bonus_point,sum(RM.redeem_point) paid,0 un_paid
FROM redeem_points RM
WHERE status=1
AND RM.start_date = '$date'
GROUP BY RM.painter_id
UNION all
SELECT RM.painter_id ,0 volumes,0 scan_point,0 bonus_point,0 paid,sum(RM.redeem_point) un_paid
FROM redeem_points RM
WHERE status=2
AND RM.start_date = '$date'
GROUP BY RM.painter_id) a,painter_users p
WHERE a.painter_id=p.id
GROUP BY a.painter_id,p.code,p.name,p.phone;
";
        $all_data = DB::select(DB::raw($query));
        $datas = collect($all_data);
        $int = $datas->map(function ($ttt) {
            return (array)$ttt;
        })->toArray();

        $painter_ids = $datas->map(function ($d) {
            return $d->painter_id;
        });
        $painters = collect(DB::table('painter_users')->whereNotIn('id', $painter_ids)->get());
        $painter = $painters->map(function ($p) use ($code){
            return [
                'painter_id' => $p->id,
                'volumes' => 0,
                'total_scan_point' => 0,
                'bonus_point' => 0,
                'redeem_point' => 0,
                'total_point' => 0,
                'transaction_code' => $code,
            ];
        })->toArray();
        DB::table('redeem_points')->insert($int);
        DB::table('redeem_points')->insert($painter);
        DB::table('volume_tranfers')->whereDate('created_at','<=', $end_date)->whereDate('created_at', '>=',$start_date)->update([
           'is_painter_redeem'=>1
        ]);
        DB::table('scan_points')->whereNotNull('painter_id')->whereDate('created_at','<=', $end_date)->whereDate('created_at', '>=',$start_date)->update([
            'is_redeem'=>1
        ]);
        DB::table('bonus_points')->whereNotNull('painter_id')->whereDate('created_at','<=', $end_date)->whereDate('created_at', '>=',$start_date)->update([
            'is_redeem'=>1
        ]);
        return 'done';
    }
}
