<?php

namespace App\Http\Controllers;

use App\Models\RedeemPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RedeemController extends Controller
{
    public function startRedeem()
    {
        $code = $this->generateRandomString();
        $start_date = '2022-03-01';
        $end_date = '2022-03-31';
        if (\request()->painter) {
            return $this->painterQuery($code, $start_date, $end_date);
        } else {
            return $this->dealerQuery($code, $start_date, $end_date);
        }
        return 'done';
    }

    function generateRandomString($prefix = false, $length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        if ($prefix) {
            $randomString = $prefix . '-' . $randomString;
        }
        $exists = DB::table('redeem_points')->where('transaction_code', $randomString)->exists();
        if ($exists) {
            $this->generateRandomString();
        }
        return strtoupper($randomString);
    }

    function painterQuery($code, $start_date, $end_date)
    {
        info($code);
        try {
            $query =
                "SELECT a.painter_id,(a.volumes) volumes,SUM(a.scan_point) total_scan_point,SUM(a.bonus_point) bonus_point,
       ((SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point) + SUM(a.forward_point))%100 ) as carry_forward_point,
 ((SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point) + SUM(a.forward_point)) - ((SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point) + SUM(a.forward_point))%100) ) as redeem_point,
 (SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point) + SUM(a.forward_point)) as total_point, '$code' as transaction_code, '$start_date' as start_date, '$end_date' as end_date
FROM (
SELECT VT.painter_id,CAST(sum(VT.painter_point) AS DECIMAL(7, 2)) volumes,0 scan_point,0 forward_point,0 bonus_point
FROM volume_tranfers VT
WHERE  DATE_FORMAT(VT.created_at, '%Y-%m-%d') <= '$end_date'
AND DATE_FORMAT(VT.created_at, '%Y-%m-%d') >= '$start_date'
  AND VT.is_painter_redeem IS NULL
AND VT.status !=2
GROUP BY VT.painter_id
UNION all
SELECT SP.painter_id, 0 volumes,CAST(sum(SP.point) AS DECIMAL(7, 2)) scan_point,0 forward_point,0 bonus_point
FROM scan_points SP
WHERE  DATE_FORMAT(SP.created_at, '%Y-%m-%d')  <= '$end_date'
AND DATE_FORMAT(SP.created_at, '%Y-%m-%d') >= '$start_date'
  AND SP.is_redeem IS NULL
GROUP BY SP.painter_id
UNION all
SELECT CF.user_id, 0 volumes,0 scan_point, CAST(sum(CF.forward_point) AS DECIMAL(7, 2)) forward_point,0 bonus_point
FROM redeem_carry_forwards CF
WHERE  DATE_FORMAT(CF.process_date, '%Y-%m-%d')  < '$start_date'
  AND CF.to_code IS NULL
  AND CF.user_type='painter'
GROUP BY CF.user_id
UNION all
SELECT BP.painter_id,0 volumes,0 scan_point, 0 forward_point, CAST(sum(BP.bonus_point) AS DECIMAL(7, 2)) bonus_point
FROM bonus_points BP
WHERE DATE_FORMAT(BP.created_at, '%Y-%m-%d')  <= '$end_date'
AND DATE_FORMAT(BP.created_at, '%Y-%m-%d') >= '$start_date'
  AND BP.is_redeem IS NULL
AND BP.soft_delete=1
GROUP BY BP.painter_id) a,painter_users p
WHERE a.painter_id=p.id
AND p.disable=1
AND p.status=1
AND p.soft_delete=1
GROUP BY a.painter_id,p.code,p.name,p.phone
";
            $all_data = DB::select(DB::raw($query));
            $datas = collect($all_data);

            $forward_point = $datas->map(function ($qp) use ($code, $start_date) {
                return [
                    'user_type' => 'painter',
                    'user_id' => $qp->painter_id,
                    'total_point' => $qp->total_point,
                    'redeem_point' => $qp->redeem_point,
                    'forward_point' => $qp->carry_forward_point,
                    'from_code' => $code,
                    'process_date' => $start_date,
                ];
            })->toArray();

            $int = $datas->map(function ($ttt) {
                return (array)$ttt;
            })->toArray();


            $painter_ids = $datas->map(function ($d) {
                return $d->painter_id;
            });
            $painters = collect(DB::table('painter_users')->where('disable', 1)->where('status', 1)->where('status', 1)->whereNotIn('id', $painter_ids)->get());
            $painter = $painters->map(function ($p) use ($code, $start_date, $end_date) {
                return [
                    'painter_id' => $p->id,
                    'volumes' => 0,
                    'total_scan_point' => 0,
                    'bonus_point' => 0,
                    'redeem_point' => 0,
                    'total_point' => 0,
                    'transaction_code' => $code,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ];
            })->toArray();

            foreach (array_chunk($int,1000) as $red)
            {
                DB::table('redeem_points')->insert($red);
            }
            foreach (array_chunk($painter,1000) as $t)
            {
                DB::table('redeem_points')->insert($t);
            }

            DB::table('volume_tranfers')->whereDate('created_at', '<=', $end_date)->whereDate('created_at', '>=', $start_date)->update([
                'is_painter_redeem' => $code
            ]);
            DB::table('scan_points')->whereNotNull('painter_id')->whereDate('created_at', '<=', $end_date)->whereDate('created_at', '>=', $start_date)->update([
                'is_redeem' => $code
            ]);
            DB::table('bonus_points')->whereNotNull('painter_id')->whereDate('created_at', '<=', $end_date)->whereDate('created_at', '>=', $start_date)->update([
                'is_redeem' => $code
            ]);
            DB::table('redeem_carry_forwards')->where('user_type','painter')->whereNull('to_code')->whereDate('process_date' ,'<', $start_date)->update([
                'to_code'=>$code
            ]);
            DB::table('redeem_carry_forwards')->insert($forward_point);
        }catch (\Exception $error) {
            DB::rollBack();
            Log::emergency($error);
            return $error;
        }
    }

    function dealerQuery($code, $start_date, $end_date)
    {
        DB::beginTransaction();
        try {
            $query =
                "SELECT a.dealer_id,(a.volumes) volumes,SUM(a.scan_point) total_scan_point,SUM(a.bonus_point) bonus_point,
       ((SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point) + SUM(a.forward_point))%1 + 1 ) as carry_forward_point,
 ((SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point) + SUM(a.forward_point)) - ((SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point) + SUM(a.forward_point))%1 + 1) ) as redeem_point,
 (SUM(a.scan_point) + SUM(a.volumes) + SUM(a.bonus_point) + SUM(a.forward_point)) as total_point, '$code' as transaction_code, '$start_date' as start_date, '$end_date' as end_date
FROM (
SELECT VT.dealer_id,CAST(sum(VT.dealer_point) AS DECIMAL(7, 2)) volumes,0 scan_point,0 forward_point,0 bonus_point
FROM volume_tranfers VT
WHERE  DATE_FORMAT(VT.created_at, '%Y-%m-%d') <= '$end_date'
AND DATE_FORMAT(VT.created_at, '%Y-%m-%d') >= '$start_date'
  AND VT.is_dealer_redeem IS NULL
AND VT.status !=2
GROUP BY VT.dealer_id
UNION all
SELECT SP.dealer_id, 0 volumes,CAST(sum(SP.point) AS DECIMAL(7, 2)) scan_point,0 forward_point,0 bonus_point
FROM scan_points SP
WHERE  DATE_FORMAT(SP.created_at, '%Y-%m-%d')  <= '$end_date'
AND DATE_FORMAT(SP.created_at, '%Y-%m-%d') >= '$start_date'
  AND SP.is_redeem IS NULL
GROUP BY SP.dealer_id
UNION all
SELECT CF.user_id, 0 volumes,0 scan_point, CAST(sum(CF.forward_point) AS DECIMAL(7, 2)) forward_point,0 bonus_point
FROM redeem_carry_forwards CF
WHERE  DATE_FORMAT(CF.process_date, '%Y-%m-%d')  < '$start_date'
  AND CF.to_code IS NULL
  AND CF.user_type='dealer'
GROUP BY CF.user_id
UNION all
SELECT BP.dealer_id,0 volumes,0 scan_point, 0 forward_point, CAST(sum(BP.bonus_point) AS DECIMAL(7, 2)) bonus_point
FROM bonus_points BP
WHERE DATE_FORMAT(BP.created_at, '%Y-%m-%d')  <= '$end_date'
AND DATE_FORMAT(BP.created_at, '%Y-%m-%d') >= '$start_date'
  AND BP.is_redeem IS NULL
AND BP.soft_delete=1
GROUP BY BP.dealer_id) a,dealer_users p
WHERE a.dealer_id=p.id
AND p.disable=1
AND p.status=1
AND p.soft_delete=1
GROUP BY a.dealer_id,p.code,p.name,p.phone;
";
            $all_data = DB::select(DB::raw($query));
            $datas = collect($all_data);

           $forward_point = $datas->map(function ($qp) use ($code, $start_date) {
                return [
                    'user_type' => 'dealer',
                    'user_id' => $qp->dealer_id,
                    'total_point' => $qp->total_point,
                    'redeem_point' => $qp->redeem_point,
                    'forward_point' => $qp->carry_forward_point,
                    'from_code' => $code,
                    'process_date' => $start_date,
                ];
            })->toArray();

            $int = $datas->map(function ($ttt) {
                return (array)$ttt;
            })->toArray();

            $dealer_ids = $datas->map(function ($d) {
                return $d->dealer_id;
            });

            $dealers = collect(DB::table('dealer_users')->where('disable', 1)->where('status', 1)->where('status', 1)->whereNotIn('id', $dealer_ids)->get());

            $dealer = $dealers->map(function ($p) use ($code, $start_date, $end_date) {
                return [
                    'dealer_id' => $p->id,
                    'volumes' => 0,
                    'total_scan_point' => 0,
                    'bonus_point' => 0,
                    'redeem_point' => 0,
                    'total_point' => 0,
                    'transaction_code' => $code,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ];
            })->toArray();

            foreach (array_chunk($int,1000) as $red)
            {
                DB::table('redeem_points')->insert($red);
            }
            foreach (array_chunk($dealer,1000) as $t)
            {
                DB::table('redeem_points')->insert($t);
            }
            DB::table('volume_tranfers')->whereDate('created_at', '<=', $end_date)->whereDate('created_at', '>=', $start_date)->update([
                'is_dealer_redeem' => $code
            ]);
            DB::table('scan_points')->whereNotNull('dealer_id')->whereDate('created_at', '<=', $end_date)->whereDate('created_at', '>=', $start_date)->update([
                'is_redeem' => $code
            ]);
            DB::table('bonus_points')->whereNotNull('dealer_id')->whereDate('created_at', '<=', $end_date)->whereDate('created_at', '>=', $start_date)->update([
                'is_redeem' => $code
            ]);
            DB::table('redeem_carry_forwards')->where('user_type','dealer')->whereNull('to_code')->whereDate('process_date' ,'<', $start_date)->update([
                'to_code'=>$code
            ]);
            DB::table('redeem_carry_forwards')->insert($forward_point);
            DB::commit();
        } catch (\Exception $error) {
            DB::rollBack();
            Log::emergency($error);
            return $error;
        }
    }
}
