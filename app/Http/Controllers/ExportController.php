<?php

namespace App\Http\Controllers;

use App\Models\BonusPoint;
use App\Models\DealerUser;
use App\Models\PainterUser;
use App\Models\Point;
use App\Models\ScanPoint;
use App\Models\Subgroup;
use App\Models\VolumeTransfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function painterInfo()
    {

//        $item_array = [];
//        $point = 0;
//        $table =  DB::select(DB::raw(
//            "(Select painter_id, null as code, point, 'scan' as table_name, created_at
//            from scan_points
//             where painter_id=280
//             UNION ALL
//             SELECT painter_id,null as code, bonus_point, 'bonus' as table_name, created_at
//             FROM bonus_points
//              where painter_id=280
//               UNION ALL
//             SELECT painter_id, code, painter_point, 'volume' as table_name, created_at
//             FROM volume_tranfers
//              where painter_id=280
//              group by code
//             ) order by created_at"));
//        foreach ($table as $t){
//            $t_t = 0;
//            if ($t->table_name !='volume'){
//                $point = $point + $t->point;
//            }
//            if ($t->table_name =='volume'){
//                $vollll = VolumeTransfer::where('code',$t->code)->get();
//                foreach ($vollll as $v){
//                    $elite_id = $this->elite_member_id($point);
//                    $item_point = $this->product_point($v->product_id, $elite_id);
//                    $t_t = $t_t + ($item_point * $v->quantity);
//                    $arr = [
//                        'date' => $t->created_at,
//                        'code' => $t->code,
//                        'painter' => 4268,
//                        'subgroup_id' => $v->product_id,
//                        'quantity' => $v->quantity,
//                        'old_single_point' => $v->painter_point / $v->quantity,
//                        'new_single_point' => $item_point,
//                        'old_point' => $v->painter_point,
//                        'real_point_should_be' => $item_point * $v->quantity,
//                        'total_point' => $point,
//                        'membership_level' => $elite_id,
//                    ];
//                }
//                $point = $point + $t_t;
//                $item_array []=$arr;
//            }
//
//        }
//        return ($item_array);
//        $data = [];
//        $total_all = 0;
//        $arr = [];
        $item_array = [];
        $arr = [];
        try {
            set_time_limit(6000);
            ini_set("pcre.backtrack_limit", "100000000");
            if (\request()->has('from_id')){
                $end = \request()->to_id;
                $all_ids = [];
                for ($from=\request()->from_id;$from<=$end;$from++){
                    $all_ids[] = (int)$from;
                }
                $painters = DealerUser::where(['status' => 1, 'disable' => 1, 'soft_delete' => 1, 'process_finish' => 0])->whereIn('id',$all_ids)->take(100)->get();
            }else{
                $painters = DealerUser::where(['status' => 1, 'disable' => 1, 'soft_delete' => 1, 'process_finish' => 0])->whereIn('id',[737])->take(100)->get();
            }
//            $painters = PainterUser::where(['status' => 1, 'disable' => 1, 'soft_delete' => 1, 'process_finish' => 0])->take(100)->get();
            $ids = $painters->pluck('id')->toArray();
            $id_for_file = '_dealer_' . $ids[0] . '_to_' . end($ids);
            foreach ($painters as $key => $painter) {
                info($key);
                $point = 0;
                $year_2021 = 0;
                $year_2022 = 0;
                $year_2023 = 0;
                $total_point = 0;
                $table = DB::select(DB::raw(
                    "(
                    Select dealer_id, null as code, point, 'scan' as table_name, created_at
                    from scan_points
                    where dealer_id = '$painter->id'
                    AND DATE_FORMAT(created_at, '%Y-%m-%d') < '2023-01-01'
                    UNION ALL
                    SELECT dealer_id,null as code, bonus_point as point, 'bonus' as table_name, created_at
                    FROM bonus_points
                    where dealer_id = '$painter->id'
                    AND soft_delete=1
                    AND DATE_FORMAT(created_at, '%Y-%m-%d') < '2023-01-01'
                    UNION ALL
                    SELECT dealer_id, code, dealer_point as point, 'volume' as table_name, created_at
                    FROM volume_tranfers
                    where dealer_id = '$painter->id'
                    AND DATE_FORMAT(created_at, '%Y-%m-%d') < '2023-01-01'
                    AND status !=2
                    group by code
             )
             order by created_at"));
                foreach ($table as $t) {
                    if ($t->table_name != 'volume') {
                        $arr = [
                            'date' => $t->created_at,
                            'code' => $t->table_name,
                            'painter' => $painter->id,
                            'subgroup_id' => null,
                            'quantity' => $t->table_name,
                            'old_single_point' => $t->table_name,
                            'new_single_point' => $t->table_name,
                            'offer_point' => $t->table_name,
                            'old_point' => $t->point,
                            'real_point_should_be' => $t->point,
                            'total_point' => $total_point,
                            'membership_level' => $painter->member_type_id,
                            'membership_level_from_point' => $this->membership_name($painter->member_type_id),
                        ];
                        $item_array [] = $arr;
                        $total_point = $t->point;
                    }
                    if ($t->table_name == 'volume') {
                        $vollll = VolumeTransfer::where(['code' => $t->code, 'dealer_id' => $painter->id])->where('status', '!=', 2)->get();
                        $t_t = 0;
                        foreach ($vollll as $v) {
                            $product = $this->product_point($v->product_id, $painter->member_type_id, $t->created_at);
                            $item_point = $product->point + $product->offer_point;
                            $t_t = $t_t + ((float)$item_point * (float)$v->quantity);
                            $old_point = $v->dealer_point && (float)$v->dealer_point >= 1 && $v->quantity ? (float)$v->dealer_point / (float)$v->quantity : 0;
                            $real_point = (float)$item_point * (float)$v->quantity;
                            $arr = [
                                'date' => $t->created_at,
                                'code' => $t->code,
                                'painter' => $painter->id,
                                'subgroup_id' => $v->product_id,
                                'quantity' => $v->quantity,
                                'old_single_point' => $old_point,
                                'new_single_point' => $item_point,
                                'offer_point' => $product->offer_point,
                                'old_point' => (float)$v->dealer_point,
                                'real_point_should_be' => $real_point,
                                'total_point' => $total_point,
                                'membership_level' => $painter->member_type_id,
                            ];
                            $item_array [] = $arr;
                            $v->update([
                                'original_painter_point' => $real_point
                            ]);
                        }
                        $total_point = $total_point+$t_t;

                        $point = $point + $t_t;
                    }
                }
                info('Dealer: ' . $painter->id . '--- Point: ' . $total_point);
                $painter->update([
                    'process_finish' => 1,
                ]);
            }

//            $fileName = time() . "_painter_info.csv";
            $fileName = time() . $id_for_file . "_info.csv";
             (new \Rap2hpoutre\FastExcel\FastExcel($item_array))->export($fileName, function ($row) {
                $amount = $row['old_point'] - $row['real_point_should_be'];
                return [
                    'Dealer ID' => $row['painter'],
                    'Code' => $row['code'],
                    'Total Point before this ' => $row['total_point'],
                    'Membership Level' => $this->membership_name($row['membership_level']),
                    'Subgroup ID' => $row['subgroup_id'],
                    'Subgroup Name' => $row['subgroup_id'] != null ? $this->product_name($row['subgroup_id']) : null,
                    'Quantity' => $row['quantity'],
                    'Point' => $row['old_point'],
                    'Offer Point' => $row['offer_point'],
                    'Item Point' => $row['old_single_point'],
//                    'Point Of' => $row['membership_level_from_point'],
                    'Item Point Should Membership Wise' => $row['new_single_point'],
                    'Original Point Should Be' => $row['real_point_should_be'],
                    'Amount Difference' => $amount,
                    'Pay Status' => $amount > 0 ? 'Over Paid' : ($amount < 0 ? 'Down Paid' : 'Payment Ok'),
                    'Date' => $row['date'],

                ];
            });
        } catch (\Exception $error) {
            return $error;
        }
        return back();
    }

    public function product_point($product_id, $elite_member_id, $date)
    {
        $parsed_date = Carbon::parse($date)->format('Y-m-d');

        $point_data = Point::where(['product_id' => $product_id, 'elite_member_id' => $elite_member_id, 'soft_delete' => 1])->whereDate('end_date', '>=', $parsed_date)->whereDate('start_date', '<=', $parsed_date)->latest()->first();
        $offer_exists = Point::where(['product_id' => $product_id, 'soft_delete' => 1, 'type' => 'OFFER'])->whereDate('end_date', '>=', $parsed_date)->whereDate('start_date', '<=', $parsed_date)->latest()->first();
        $array = [
            'point' => $point_data && $point_data->point ? $point_data->point : 0,
            'offer_point' => $offer_exists && $offer_exists->point ? $offer_exists->point : 0
        ];
        return (object)$array;
    }

    public function elite_member_id($point)
    {
        return $point < 3500 ? 1 : ($point < 13000 ? 2 : ($point < 30000 ? 6 : 7));
    }

    public function membership_name($id)
    {
        if ($id == 8) {
            return 'CF';
        } else if ($id == 9) {
            return 'NCF';
        }
//        else if ($id == 6) {
//            return 'Gold';
//        } else {
//            return 'Platinum';
//        }
    }

    public function membership_by_point($product_id, $point)
    {
        $point_data = Point::where(['product_id' => $product_id, 'point' => $point, 'soft_delete' => 1])->first();
        if ($point_data) {
            return $this->membership_name($point_data->elite_member_id);
        } else {
            return 'Wrong Point';
        }
    }

    public function product_name($id)
    {
        return Subgroup::find($id)->subgroup_name;
    }

}
