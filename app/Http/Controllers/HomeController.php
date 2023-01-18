<?php

namespace App\Http\Controllers;

use App\Models\Basegroup;
use App\Models\BonusPoint;
use App\Models\DealerUser;
use App\Models\Invoice;
use App\Models\PainterUser;
use App\Models\Product;
use App\Models\RedeemPoint;
use App\Models\RedeemtionHistory;
use App\Models\ReedemPoint;
use App\Models\ScanPoint;
use App\Models\Stock;
use App\Models\Subgroup;
use App\Models\UserReport;
use App\Models\VolumeTransfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function invoice()
    {
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");
//        $invoices = Invoice::whereNull('basegroup_id')->get();
        $invoices = DB::table('invoices')->whereNull('basegroup_id')->get();
        foreach ($invoices as $invoice) {
//            $subgroup = Subgroup::where('subgroup_code', substr($invoice->product_code, 0, 4))->first();
            $subgroup = DB::table('subgroups')->where('subgroup_code', substr($invoice->product_code, 0, 4))->first();
            if (!$subgroup->basegroup_id) {
                $basegroup = DB::table('basegroups')->insertGetId([
                    'basegroup_code' => $subgroup->subgroup_code,
                    'basegroup_name' => $subgroup->subgroup_name,
                    'delivery_percentage' => 0,
                ]);
//                $basegroup = Basegroup::create([
//                    'basegroup_code'=>$subgroup->subgroup_code,
//                    'basegroup_name'=>$subgroup->subgroup_name,
//                    'delivery_percentage'=>0,
//                ]);
                $subgroup->update(['basegroup_id' => $basegroup->id]);
            }
            $condition = ($invoice->pack_size * ($invoice->quantity));
            $invoice->update([
                'basegroup_id' => $subgroup->basegroup_id,
                'subgroup_id' => $subgroup->id,
                'total_volume' => $condition
            ]);
        }
        return 'Done';

//        $ids = Invoice::whereNull('basegroup_id')->take(10000)->pluck('id');
//        $invoices = Invoice::whereIn('dealer_id', [2201,1100])->get()->groupBy(function ($item) {
//            return substr($item->product_code, 0, 4);
//        });
//        foreach ($invoices as $key => $invoice) {
//            $subgroup = Subgroup::where('subgroup_code', $key)->first();
//            if (!$subgroup->basegroup_id){
//                $basegroup = Basegroup::create([
//                    'basegroup_code'=>$subgroup->subgroup_code,
//                    'basegroup_name'=>$subgroup->subgroup_name,
//                    'delivery_percentage'=>0,
//                ]);
//                $subgroup->update(['basegroup_id'=>$basegroup->id]);
//            }
//            foreach ($invoice as $in) {
//                $condition = ($in->pack_size* round($in->quantity));
//                $in->update([
//                    'basegroup_id'=> $subgroup->basegroup_id,
//                    'subgroup_id'=>$subgroup->id,
//                    'total_volume'=>$condition
//                ]);
//            }
//
//        }
//        return 'done';

    }

    public function volume()
    {
        $volumes = VolumeTransfer::whereNull('basegroup_id')->take(10000)->get();
        foreach ($volumes as $volume) {
            $subgroup = Subgroup::find($volume->product_id);
            if (!$subgroup->basegroup_id) {
                $basegroup = Basegroup::create([
                    'basegroup_code' => $subgroup->subgroup_code,
                    'basegroup_name' => $subgroup->subgroup_name,
                    'delivery_percentage' => 0,
                ]);
                $subgroup->update(['basegroup_id' => $basegroup->id]);
            }
            $volume->update([
                'basegroup_id' => $subgroup->basegroup_id,
            ]);
        }
        return 'Done Volume';
//        $ids = VolumeTransfer::where('basegroup_id')->take(10000)->pluck('id');
//        $volumes = VolumeTransfer::whereIn('dealer_id', [2201,1100])->get()->groupBy('product_id');
//        foreach ($volumes as $key => $volume) {
//            $subgroup = Subgroup::find($key);
//            if (!$subgroup->basegroup_id){
//                $basegroup = Basegroup::create([
//                    'basegroup_code'=>$subgroup->subgroup_code,
//                    'basegroup_name'=>$subgroup->subgroup_name,
//                    'delivery_percentage'=>0,
//                ]);
//                $subgroup->update(['basegroup_id'=>$basegroup->id]);
//            }
//            foreach ($volume as $in) {
//                $in->update([
//                    'basegroup_id'=> $subgroup->basegroup_id,
//                ]);
//            }
//
//        }
//        return 'done';
    }

    public function useless()
    {
        $all = [];
//        $dealers = Invoice::whereNotIn('dealer_id', Stock::query()->pluck('dealer_id'))->groupBy('dealer_id')->take(100)->get();
        $dealers = Invoice::whereIN('dealer_id', [2201, 1011, 2000])->groupBy('dealer_id')->get();
        foreach ($dealers as $key1 => $dealer) {
            $invoices = Invoice::where('dealer_id', $dealer->dealer_id)->get()->groupBy(function ($item) {
                return substr($item->product_code, 0, 4);
            });
            foreach ($invoices as $key => $invoice) {
                $subgroup = Subgroup::where('subgroup_code', $key)->first();
                $in_total = 0;
                if (true) {
                    if (!$subgroup->basegroup_id) {
                        $basegroup = Basegroup::create([
                            'basegroup_code' => $subgroup->subgroup_code,
                            'basegroup_name' => $subgroup->subgroup_name,
                            'delivery_percentage' => 0,
                        ]);
                        $subgroup->update(['basegroup_id' => $basegroup->id]);
                    }
                    foreach ($invoice as $in) {
                        $condition = ($in->pack_size * round($in->quantity));
                        DB::table('stock_ins')->insert([
                            'dealer_id' => $dealer->dealer_id,
                            'invoice_id' => $in->id,
                            'pack_size' => $in->pack_size,
                            'quantity' => $in->quantity,
                            'basegroup_id' => $subgroup->basegroup_id,
                            'subgroup_id' => $subgroup->id,
//                            'total_volume' => $condition < 1 ? 0 : round($condition,2),
                            'total_volume' => round($condition, 2),
                            'in_time' => $in->created_at,
                            'created_at' => Carbon::now()
                        ]);
                        $in_total += round($condition, 2);
                    }

                    $stock_in = DB::table('stock_ins')
                        ->where('dealer_id', $dealer->dealer_id)
                        ->where('basegroup_id', $subgroup->basegroup_id)
                        ->sum('total_volume');

                    $stock_out = DB::table('volume_tranfers')
                        ->where('dealer_id', $dealer->dealer_id)
                        ->where('product_id', $subgroup->basegroup_id)
                        ->where('status', '!=', 2)
                        ->where('soft_delete', 1)
                        ->sum('quantity');

                    $stock = $stock_in - $stock_out;
                    $all[] = [
                        'dealer_id' => $dealer->dealer_id,
                        'subgroup_id' => $subgroup->basegroup_id,
                        'stock' => round($stock, 2),
                        'year' => 2022,
                        'created_at' => Carbon::now()
                    ];
                }

            }
            Log::info($key1);
        }
        DB::table('stocks')->insert($all);
//       Stock::create($all);
        return 'success';
    }

    public function stockUpdate()
    {
        $dealers = Invoice::whereIN('dealer_id', [2201, 1011, 2000])->groupBy('dealer_id')->get();
        foreach ($dealers as $key1 => $dealer) {
            $invoices = Invoice::where('dealer_id', $dealer->dealer_id)->get()->groupBy('basegroup_id');
            foreach ($invoices as $key => $invoice) {
                $in_total = 0;
                foreach ($invoice as $in) {
                    $in_total += $in->total_volume;
                }
                $stock_out = DB::table('volume_tranfers')
                    ->where('dealer_id', $dealer->dealer_id)
                    ->where('basegroup_id', $key)
                    ->where('status', '!=', 2)
                    ->where('soft_delete', 1)
                    ->sum('quantity');
                $stock = $in_total - $stock_out;
                $test = [
                    'dealer_id' => $dealer->dealer_id,
                    'subgroup_id' => $key,
                    'name' => Basegroup::find($key)->basegroup_name,
                    'stock' => round($stock, 2),
                    'year' => 2022,
                    'created_at' => Carbon::now()
                ];
                DB::table('stocks')->insert($test);
            }
            Log::info($key1);
        }

        return 'success';
    }

    public function invoiceUpdate()
    {
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");
        $invoices = DB::table('invoices')->whereNull('basegroup_id')->get();
        foreach ($invoices as $invoice) {
            $subgroup = DB::table('subgroups')->where('subgroup_code', substr($invoice->product_code, 0, 4))->first();
            if (!$subgroup->basegroup_id) {
                $basegroup = DB::table('basegroups')->insertGetId([
                    'basegroup_code' => $subgroup->subgroup_code,
                    'basegroup_name' => $subgroup->subgroup_name,
                    'delivery_percentage' => 0,
                ]);
                DB::table('subgroups')->where('subgroup_code', substr($invoice->product_code, 0, 4))->update(['basegroup_id' => $basegroup]);
            }
            $condition = ($invoice->pack_size * ($invoice->quantity));
            DB::table('invoices')->where('id', $invoice->id)->update([
                'basegroup_id' => $subgroup->basegroup_id,
                'subgroup_id' => $subgroup->id,
                'total_volume' => $condition
            ]);
        }
        return 'Done';

    }

    public function volumeUpdate()
    {
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");
        $volumes = DB::table('volume_tranfers')->whereNull('basegroup_id')->get();
        foreach ($volumes as $volume) {
            $subgroup = DB::table('subgroups')->where('id', $volume->product_id)->first();
            if (!$subgroup->basegroup_id) {
                $basegroup = DB::table('basegroups')->insertGetId([
                    'basegroup_code' => $subgroup->subgroup_code,
                    'basegroup_name' => $subgroup->subgroup_name,
                    'delivery_percentage' => 0,
                ]);
                DB::table('subgroups')->where('id', $volume->product_id)->update(['basegroup_id' => $basegroup]);
            }
            DB::table('volume_tranfers')->where('id', $volume->id)->update([
                'basegroup_id' => $subgroup->basegroup_id,
            ]);
        }
        return 'Done Volume';
    }

    public function invoiceP(Request $request)
    {
        $erp_api_key = $request->erp_api_key;
        $code = $request->code;
        $name = $request->name;
        $email = $request->email;
        $phone = $request->phone;
        $rocket_number = $request->rocket_number;
        $alternative_number = $request->alternative_number;
        $date = $request->date;
        $invoice = $request->invoice;
        $product_code = $request->product_code;
        $product_name = $request->product_name;
        $pack_size = $request->pack_size;
        $shade_name = $request->shade_name;
        $quantity = $request->quantity;
        $net_amount = $request->net_amount;
        $fixed = new Constants();

        $code = substr($product_code, 0, 4);
        $subgroup = DB::table('subgroups')->where('subgroup_code', $code)->first();
        if (!$subgroup->basegroup_id) {
            $basegroup = DB::table('basegroups')->insertGetId([
                'basegroup_code' => $subgroup->code,
                'basegroup_name' => $subgroup->subgroup_name,
                'delivery_percentage' => 0,
            ]);
            DB::table('subgroups')->where('id', $subgroup->id)->update(['basegroup_id' => $basegroup]);
        }
        $total_volume = $pack_size * $quantity;
        //dd($fixed->geterp_api_key());
        if ($fixed->geterp_api_key() == $erp_api_key) {
            $dealer = DealerUser::where('code', $code)->select('id')->get()->first();


            if (!$dealer) {

                $data = [
                    'error' => 'DEALER CODE NOT MATCHED.',
                ];
                return response()->json(['data' => $data], 200);

            }

            $dealer_id = DB::table('invoices')->where('dealer_id', $dealer['id'])->where('date', $date)->where('invoice', $invoice)->where('product_code', $product_code)->select('id')->get()->last();
            //dd($dealer_id->id);
            if ($dealer_id) {
                DB::table('invoices')->where('id', $dealer_id->id)->update([
                    'invoice' => $invoice,
                    'basegroup_id' => $subgroup->basegroup_id,
                    'subgroup_id' => $subgroup->id,
                    'product_code' => $product_code,
                    'product_name' => $product_name, 'date' => $date,
                    'pack_size' => $pack_size,
                    'shade_name' => $shade_name,
                    'quantity' => $quantity,
                    'net_amount' => $net_amount,
                    'total_volume' => $total_volume,
                ]);
            } else {
                DB::table('invoices')->insert(['dealer_id' => $dealer['id'],
                    'invoice' => $invoice,
                    'basegroup_id' => $subgroup->basegroup_id,
                    'subgroup_id' => $subgroup->id,
                    'product_code' => $product_code,
                    'product_name' => $product_name, 'date' => $date,
                    'pack_size' => $pack_size,
                    'shade_name' => $shade_name,
                    'quantity' => $quantity, 'net_amount' => $net_amount,
                    'total_volume' => $total_volume,
                ]);
            }
            $data = [
                'message' => 'Success',
            ];


            return response()->json(['data' => $data], 200);
        } else {
            $data = [
                'error' => 'Unauthorized Access.',
            ];
            return response()->json(['data' => $data], 200);
        }

    }

    public function get_invoice($user_token)
    {
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");

        if (!$user_token) {
            $data = [
                'error' => 'NO USER TOKEN SEND.',
            ];
            return response()->json(['data' => $data], 200);
        } else {
            $dealer = DealerUser::where('user_token', $user_token)
                ->select('id', 'password', 'status', 'code',
                    'name', 'phone')->get()->last();
            if ($dealer) {
                $live_order = [];
                $invoices = DB::table('invoices')
                    ->where('dealer_id', $dealer['id'])
                    ->orderBy('created_at', 'desc')
                    ->select('date', 'invoice')
                    ->distinct('invoice')->get()->toArray();
                if (!$invoices) {
                    $data = [
                        'message' => 'NO INVOICE ADDED.',
                    ];
                    return response()->json(['data' => $data], 200);
                }
                foreach ($invoices as $key => $invoice) {
                    $invoice_group = DB::table('invoices')->where('invoice', $invoice->invoice);
                    $feedback_l = $invoice_group->count('invoice');
                    $quantity = $invoice_group->sum('quantity');
                    $points = $invoice_group->sum('point');
                    $live = [
                        'invoice_id' => $invoice->invoice,
                        'invoice_date' => $invoice->date,
                        'quantity' => $quantity,
                        'point' => $points,
                        'no_of_product' => $feedback_l,
                    ];
                    $live_order[] = $live;
                }
            }
        }

        return response()->json(['data' => $live_order], 200);
    }

    public function get_volume_transfer_history(Request $request)
    {

        $app_identifier = $request->app_identifier;
        $user_token = $request->header('USER-TOKEN');

        $now = Carbon::now();
        $month = $now->year . '-' . $now->month;
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->today();

        $all_dates = [];

        // get all dates
        while ($start->lte($end)) {
            $all_dates[] = $start->copy();
            $start->addDay();
        }

        $all_dates = array_reverse($all_dates);

        if ($user_token) {
            if ($app_identifier == 'com.ets.elitepaint.dealer') {
                $dealer = DealerUser::where('user_token', $user_token)
                    ->select('id', 'password', 'status', 'code',
                        'name', 'phone')->get()->last();
                //dd($dealer);
                if ($dealer) {
                    $allData = [];
                    foreach ($all_dates as $date) {
                        $actual_date = $date->todateString();
                        $complete_total = VolumeTranfer::where('dealer_id', $dealer['id'])->where('status', '!=', 2)
                            ->where('created_at', 'LIKE', '%' . $actual_date . '%')->
                            distinct('code')->
                            count('code');
                        $total_codes = VolumeTranfer::where('dealer_id', $dealer['id'])
                            ->where('created_at', 'LIKE', '%' . $actual_date . '%')->
                            distinct('code')->
                            select('id', 'code', 'dealer_point')->get();

                        //dd($total_codes);
                        $total_codess = VolumeTranfer::where('dealer_id', $dealer['id'])->where('status', '!=', 2)
                            ->where('created_at', 'LIKE', '%' . $actual_date . '%')->
                            select('id', 'code', 'dealer_point')->get()->toArray();
                        $total_ltr = 0;
                        $total_point = 0;
                        foreach ($total_codess as $total_codeaa) {
                            //dd($total_code['id']);

                            $total_point += $total_codeaa['dealer_point'];

                        }
                        foreach ($total_codes as $total_code) {
                            //dd($total_code['id']);
                            $volum = VolumeTranfer::where('id', $total_code['id'])
                                ->select('id', 'product_id', 'quantity')->get()->last();
                            $product = Product::where('id', $volum['product_id'])
                                ->select('id', 'pack_size_id')->get()->last();
                            $pack_size = Pack::where('id', $product['pack_size_id'])
                                ->select('id', 'pack_size')->get()->last();
                            $totals = $volum['quantity'] * $pack_size['pack_size'];
                            $total_ltr += $volum['quantity'];
                            //$total_point += $total_code['dealer_point'];

                        }
                        //dd($total_point);
                        if ($complete_total > 0) {
                            $data = ['date' => $date->todateString(),
                                'day' => $date->format('l'),
                                'enable' => ($complete_total == null) ? 0 : 1,
                                'volume_transfer' => $complete_total,
                                'ltr' => $total_ltr,
                                'points' => $total_point,
                            ];
                            $allData[] = $data;
                        }

                    }
                    if (empty($allData)) {
                        //dd('s');
                        $datas = [
                            'message' => 'NO DATE AVAILABLE',
                        ];
                        return response()->json(['data' => $datas], 200);
                    }
                } else {
                    $data = [
                        'error' => 'USER TOKEN NOT MATCHED.',
                    ];
                    return response()->json(['data' => $data], 200);
                }

            } elseif ($app_identifier == 'com.ets.elitepaint.painter') {
                $dealer = PainterUser::where('user_token', $user_token)
                    ->select('id', 'password', 'status', 'code',
                        'name', 'phone')->get()->last();
                //dd($dealer);
                if ($dealer) {
                    foreach ($all_dates as $date) {
                        $actual_date = $date->todateString();
                        $scanpoint_year = ScanPoint::where('painter_id', $dealer['id'])
                            ->where('created_at', 'LIKE', '%' . $actual_date . '%')
                            ->select('id', 'bar_code_id')->get()->toArray();
                        $no_of_scan_month = count($scanpoint_year);
                        $all_total_year = 0;
                        //dd($dealer_list);
                        foreach ($scanpoint_year as $scanpoint) {
                            $barcode = BarCode::where('id', $scanpoint['bar_code_id'])
                                ->select('id', 'product_id', 'point')->get()->last();

                            $all_total_year += $barcode['point'];
                        }
                        $data = ['date' => $date->todateString(),
                            'day' => $date->format('l'),
                            'enable' => ($scanpoint_year == null) ? 0 : 1,
                            'points' => $all_total_year,
                            'no_of_scan' => $no_of_scan_month
                        ];
                        $allData[] = $data;
                    }
                } else {
                    $data = [
                        'error' => 'USER TOKEN NOT MATCHED.',
                    ];
                    return response()->json(['data' => $data], 200);
                }

            }


        } else {
            $data = [
                'error' => 'NO USER TOKEN SEND.',
            ];
            return response()->json(['data' => $data], 200);
        }

        return response()->json(['data' => $allData], 200);
    }

    public function dealerUser()
    {
        $reedem_point = RedeemPoint::all()->sum('redeem_point');
        $scan_point = ScanPoint::all()->sum('point');
        $volume_transfer = VolumeTransfer::all()->sum(function ($row) {
            return $row->dealer_point + $row->painter_point;
        });
        $bonus_point = BonusPoint::where('soft_delete', 1)->sum('bonus_point');

        return ($bonus_point + $volume_transfer + $scan_point) - $reedem_point;

        return 'deleted';
    }

    public function updateDealer()
    {

        $all_dealers = DB::table('dealer_users')->get();
        foreach ($all_dealers as $dealer) {

            $scan_point = DB::table('scan_points')->where('dealer_id', $dealer->id)->sum('point');
            $volume_transfer_point = DB::table('volume_tranfers')->where('dealer_id', $dealer->id)->where('status', '!=', 2)->sum('dealer_point');
            $bonus_point = DB::table('bonus_points')->where('dealer_id', $dealer->id)->sum('bonus_point');
            $total_earning_point = $scan_point + $volume_transfer_point + $bonus_point;

            $total_redeem_point = DB::table('redeem_points')->where('dealer_id', $dealer->id)->where('status', '!=', 2)->sum('redeem_point');


            $total_balance = $total_earning_point - $total_redeem_point;

            if ($total_balance) {
                DB::table('dealer_users')
                    ->where('id', $dealer->id)
                    ->update([
                        'total_earn_point' => $total_earning_point,
                        'total_redeem_point' => $total_redeem_point,
                        'balance' => $total_balance
                    ]);
            }
        }
        echo 'Dealer Update Done';
    }
    public function updatePainter()
    {

        $all_painters = DB::table('painter_users')->get();
        foreach ($all_painters as $painter) {

            $scan_point = DB::table('scan_points')->where('painter_id', $painter->id)->sum('point');
            $volume_transfer_point = DB::table('volume_tranfers')->where('painter_id', $painter->id)->where('status', '!=', 2)->sum('painter_point');
            $bonus_point = DB::table('bonus_points')->where('soft_delete', '=',1)->where('painter_id', $painter->id)->sum('bonus_point');

            $total_earning_point = $scan_point + $volume_transfer_point + $bonus_point;
            $total_redeem_point = DB::table('redeem_points')->where('painter_id', $painter->id)->where('status', '=', 1)->sum('redeem_point');


            $total_balance = $total_earning_point - $total_redeem_point;
//            $painter_level = \App\Classes\LevelCalculation::painter_level($painter->id);
            if ($total_balance) {
                DB::table('painter_users')
                    ->where('id', $painter->id)
                    ->update([
                        'total_earn_point' => $total_earning_point,
                        'total_redeem_point' => $total_redeem_point,
                        'balance' => $total_balance,
                        'level' => 0
                    ]);
            }
        }
        echo 'Painter Update Done';
    }

    public function checkDate()
    {
        $this->doSommation();
        return 'completed';
    }

    public function doSommation()
    {
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");
        $reports = UserReport::whereNull('earned_point')->get();
        foreach ($reports as $report){
            $earned_point = $report->scan_point + $report->bonus_point + $report->volume_point;
            $balance = $earned_point-$report->redeem_point;
            $report->update([
               'earned_point' =>$earned_point,
//               'balance' =>$balance,
            ]);
        }
        return 'updated successfully';
    }

    public function scanpoint()
    {
        $dealers_data =  ScanPoint::whereYear('created_at' , '<', 2023)->whereNotNull('painter_id')->where('is_complete', false)->groupby('painter_id')->take(1000)->get();
        foreach ($dealers_data as $dealer){
            $month_data = ScanPoint::where('painter_id' , $dealer->painter_id)->where('is_complete', false)->select(DB::raw('count(id) as `data`'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'), DB::raw('SUM(point) as point'), 'painter_id')
                ->groupby('year','month')
                ->get();
            foreach ($month_data as $month){
                $exist = UserReport::where(['user_class'=>PainterUser::class,  'user_id'=>$month->painter_id, 'date'=>$month->new_date,
                    'month'=>$month->month,
                    'year'=>$month->year,])->first();
                if ($exist){
                    $exist->update([
                        'scan_point'=>$month->point
                    ]);
                }else{
                    UserReport::create([
                        'user_class'=>PainterUser::class,
                        'user_id'=>$month->painter_id,
                        'date'=>$month->new_date,
                        'month'=>$month->month,
                        'year'=>$month->year,
                        'scan_point'=>$month->point,
                    ]);
                }
            }
            ScanPoint::where('painter_id' , $dealer->painter_id)->where('is_complete', false)->update(['is_complete'=>1]);
        }
    }
    public function volumepoint()
    {
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");
        $dealers_data =  VolumeTransfer::whereYear('created_at' , '<', 2023)->where('status','!=',2)->whereNotNull('painter_id')->groupby('painter_id')->get();
        foreach ($dealers_data as $dealer){
            $month_data = VolumeTransfer::where('painter_id' , $dealer->painter_id)->where('status','!=',2)->select(DB::raw('count(id) as `data`'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'), DB::raw('SUM(painter_point) as point'), 'painter_id')
                ->groupby('year','month')
                ->get();
            foreach ($month_data as $month){
                $exist = UserReport::where(['user_class'=>PainterUser::class,  'user_id'=>$month->painter_id, 'date'=>$month->new_date,
                    'month'=>$month->month,
                    'year'=>$month->year,])->first();
                if ($exist){
                    $exist->update([
                        'volume_point'=>$month->point
                    ]);
                }else{
                    UserReport::create([
                        'user_class'=>PainterUser::class,
                        'user_id'=>$month->painter_id,
                        'date'=>$month->new_date,
                        'month'=>$month->month,
                        'year'=>$month->year,
                        'volume_point'=>$month->point,
                    ]);
                }
            }
//            VolumeTransfer::where('painter_id' , $dealer->painter_id)->where('status','!=',2)->update(['is_complete'=>1]);
        }
    }

    public function bonuspoint()
    {
        $dealers_data =  BonusPoint::whereYear('created_at' , '<', 2023)->where('soft_delete',1)->whereNotNull('painter_id')->groupby('painter_id')->take(1000)->get();
        foreach ($dealers_data as $dealer){
            $month_data = BonusPoint::where('painter_id' , $dealer->painter_id)->where('soft_delete',1)->select(DB::raw('count(id) as `data`'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'), DB::raw('SUM(bonus_point) as point'), 'painter_id')
                ->groupby('year','month')
                ->get();
            foreach ($month_data as $month){
                $exist = UserReport::where(['user_class'=>PainterUser::class,  'user_id'=>$month->painter_id, 'date'=>$month->new_date,
                    'month'=>$month->month,
                    'year'=>$month->year,])->first();
                if ($exist){
                    $exist->update([
                        'bonus_point'=>$month->point
                    ]);
                }else{
                    UserReport::create([
                        'user_class'=>PainterUser::class,
                        'user_id'=>$month->painter_id,
                        'date'=>$month->new_date,
                        'month'=>$month->month,
                        'year'=>$month->year,
                        'bonus_point'=>$month->point,
                    ]);
                }
            }
        }
    }

    public function redeempoint()
    {
        $dealers_data =  RedeemPoint::whereYear('created_at' , '<', 2023)->where('status',1)->whereNotNull('dealer_id')->groupby('dealer_id')->take(1000)->get();
        foreach ($dealers_data as $dealer){
            $month_data = VolumeTransfer::where('dealer_id' , $dealer->dealer_id)->where('status',1)->select(DB::raw('count(id) as `data`'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'), DB::raw('SUM(bonus_point) as point'), 'dealer_id')
                ->groupby('year','month')
                ->get();
            foreach ($month_data as $month){
                $exist = UserReport::where(['user_class'=>DealerUser::class,  'user_id'=>$month->dealer_id, 'date'=>$month->new_date,
                    'month'=>$month->month,
                    'year'=>$month->year,])->first();
                if ($exist){
                    $exist->update([
                        'volume_point'=>$month->point
                    ]);
                }else{
                    UserReport::create([
                        'user_class'=>DealerUser::class,
                        'user_id'=>$month->dealer_id,
                        'date'=>$month->new_date,
                        'month'=>$month->month,
                        'year'=>$month->year,
                        'volume_point'=>$month->point,
                    ]);
                }
            }
        }
    }

    public function history()
    {
        $one = UserReport::where('year',2022)->whereIn('month',[1,2,3])->sum('earned_point');
        $two = UserReport::where('year',2021)->whereIn('month', [12])->sum('earned_point');
        $redeem_point = 528606;
        RedeemtionHistory::create([
           'earned'=> $one + $two,
           'from'=> '2021-12-01',
           'to'=> '2022-03-31',
           'redeem'=> $redeem_point,
           'balance'=> ($one + $two) - $redeem_point,
        ]);
        return 'done';
    }
}
