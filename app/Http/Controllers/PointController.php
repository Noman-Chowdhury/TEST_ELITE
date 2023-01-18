<?php

namespace App\Http\Controllers;

use App\Models\BonusPoint;
use App\Models\DealerUser;
use App\Models\PainterUser;
use App\Models\RedeemPoint;
use App\Models\Redeemtion;
use App\Models\ScanPoint;
use App\Models\VolumeTransfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PointController extends Controller
{
    public function PainterTest()
    {
//        return ScanPoint::whereNotNull('painter_id')->whereDate('created_at', '<=', '2022-03-31')->sum('point');
        $dates = RedeemPoint::query()->whereNotNull('painter_id')->select('start_date', 'end_date')->groupBy('start_date', 'end_date')->get();
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");

        $uuid = Str::random(4) . uniqid('PO');
        $ex = Redeemtion::where('code', $uuid)->exists();
        if ($ex) {
            $uuid = $uuid . '1';
        }
        $dealers = PainterUser::where('level', '!=', 'completed')->take(1600)->get();

        foreach ($dealers as $dealer) {
            foreach ($dates as $date) {
                $startDate = $date->start_date;
                $endDate = $date->end_date;
                $start = Carbon::parse($startDate)->format('Y-m-d');
                $end = Carbon::parse($endDate)->format('Y-m-d');
                if ($startDate == '2021/12/01') {
                    $scan_point = $dealer->scanPoints()->whereDate('created_at', '<=', $end)->sum('point');
                    $volume = $dealer->volumePoints()->whereDate('created_at', '<=', $end)->sum('painter_point');
                    $bonus_point = $dealer->bonusPoints()->whereDate('created_at', '<=', $end)->sum('bonus_point');
                } else {
                    $scan_point = $dealer->scanPoints()->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->sum('point');
                    $volume = $dealer->volumePoints()->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->sum('painter_point');
                    $bonus_point = $dealer->bonusPoints()->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->sum('bonus_point');
                }

                $total_point = $scan_point + $volume + $bonus_point;
                $redeem = $dealer->redeemPoints()->where(['end_date' => $endDate, 'status' => 1])->first()->redeem_point ?? 0;
                $processing = $dealer->redeemPoints()->where(['end_date' => $endDate, 'status' => 2])->first()->redeem_point ?? 0;
                $code = $uuid;
                Redeemtion::create([
                    'volumes' => $volume,
                    'total_scan_point' => $scan_point,
                    'bonus_point' => $bonus_point,
                    'total_point' => $total_point,
                    'painter_id' => $dealer->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'code' => $code,
                    'complete_redeem_point' => $redeem,
                    'processing_redeem_point' => $processing,
                    'total_redeem_point' => $redeem + $processing,
                    'balance' => $total_point - ($redeem + $processing),
                    'status' => 'completed'
                ]);
            }
            $dealer->update(['level' => 'completed']);
        }
        return 'Painter Data Processed';
    }

    public function DealerTest()
    {
//        return ScanPoint::whereNotNull('painter_id')->whereDate('created_at', '<=', '2022-03-31')->sum('point');
        $dates = RedeemPoint::query()->whereNotNull('dealer_id')->select('start_date', 'end_date')->groupBy('start_date', 'end_date')->get();
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");

        $uuid = Str::random(4) . uniqid('PO');
        $ex = Redeemtion::where('code', $uuid)->exists();
        if ($ex) {
            $uuid = $uuid . '1';
        }
        $dealers = DealerUser::where('level', '!=', 'completed')->take(1600)->get();

        foreach ($dealers as $dealer) {
            foreach ($dates as $date) {
                $startDate = $date->start_date;
                $endDate = $date->end_date;
                $start = Carbon::parse($startDate)->format('Y-m-d');
                $end = Carbon::parse($endDate)->format('Y-m-d');
                if ($startDate == '2021/12/01') {
                    $scan_point = $dealer->scanPoints()->whereDate('created_at', '<=', $end)->sum('point');
                    $volume = $dealer->volumePoints()->whereDate('created_at', '<=', $end)->sum('dealer_point');
                    $bonus_point = $dealer->bonusPoints()->whereDate('created_at', '<=', $end)->sum('bonus_point');
                } else {
                    $scan_point = $dealer->scanPoints()->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->sum('point');
                    $volume = $dealer->volumePoints()->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->sum('dealer_point');
                    $bonus_point = $dealer->bonusPoints()->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->sum('bonus_point');
                }

                $total_point = $scan_point + $volume + $bonus_point;
                $redeem = $dealer->redeemPoints()->where(['end_date' => $endDate, 'status' => 1])->first()->redeem_point ?? 0;
                $processing = $dealer->redeemPoints()->where(['end_date' => $endDate, 'status' => 2])->first()->redeem_point ?? 0;
                $code = $uuid;
                Redeemtion::create([
                    'volumes' => $volume,
                    'total_scan_point' => $scan_point,
                    'bonus_point' => $bonus_point,
                    'total_point' => $total_point,
                    'dealer_id' => $dealer->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'code' => $code,
                    'complete_redeem_point' => $redeem,
                    'processing_redeem_point' => $processing,
                    'total_redeem_point' => $redeem + $processing,
                    'balance' => $total_point - ($redeem + $processing),
                    'status' => 'completed'
                ]);
            }
            $dealer->update(['level' => 'completed']);
        }
        return 'Dealer Data Processed';
    }

    public function updateVolumeData()
    {
//        $painter =  PainterUser::query()->pluck('id');
//        return VolumeTransfer::query()->where('status', '!=',2)->whereDate('created_at','<=','2022-03-31')->sum('painter_point');
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");

//        $dealer_id = DealerUser::query()->pluck('id');
//        return BonusPoint::query()->whereNotIn('dealer_id',$dealer_id)->groupBy('dealer_id')->pluck('dealer_id');

        $dates = RedeemPoint::query()->whereNotNull('painter_id')->select('start_date', 'end_date')->groupBy('start_date', 'end_date')->get();
        foreach ($dates as $date) {
            $startDate = $date->start_date;
            $endDate = $date->end_date;
            $start = Carbon::parse($startDate)->format('Y-m-d');
            $end = Carbon::parse($endDate)->format('Y-m-d');

//            $volumes_painter_id = VolumeTransfer::where('status', 2)->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->groupBy('painter_id')->pluck('painter_id');
            $painters = PainterUser::query()->get();
            foreach ($painters as $painter) {
                if ($startDate == '2021/12/01') {
                     $volume = $painter->volumePoints()->whereDate('created_at', '<=', $end)->sum('painter_point');
                } else {
                    $volume = $painter->volumePoints()->whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->sum('painter_point');
                }
                $redemption = Redeemtion::query()->where([
                    'painter_id' => $painter->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ])->first();

                $total = $volume + $redemption->total_scan_point + $redemption->bonus_point;
                $redemption->update([
                    'volumes' => $volume,
                    'total_point' => $total,
                    'balance' => $total - $redemption->total_redeem_point
                ]);

            }
        }
        return 'painter_data_updated';
    }
}
