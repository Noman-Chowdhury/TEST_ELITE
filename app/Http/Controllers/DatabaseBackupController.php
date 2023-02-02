<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Kreait\Firebase\Exception\DatabaseException;
use Spatie\Backup\BackupDestination\BackupDestination;

class DatabaseBackupController extends Controller
{
    private $database;

    public function __construct()
    {
        $this->database = \App\Services\FirebaseService::connect();
    }

    /**
     * @throws DatabaseException
     */
    public function create(Request $request)
    {
        $message='';
        $backups = $this->database->getReference('backup')->getValue();
        $today_data_count =array_key_exists(Carbon::now()->format('Y-m-d'),$backups) ? count($backups[Carbon::now()->format('Y-m-d')]) : 0;
        if (DateTime::createFromFormat('h:i a', Carbon::now()->format('h:i a')) > DateTime::createFromFormat('h:i a', "08:30 am") && DateTime::createFromFormat('h:i a', Carbon::now()->format('h:i a')) < DateTime::createFromFormat('h:i a', "11:30 am")) {
            if ($today_data_count == 0){
                (object) $message = [
                    'content'=> 'You can take backup 2 times From 8:30am to 11:30am and 5:30pm to 10:30pm',
                    'enable'=> true
                ];
            }
            if ($today_data_count == 1){
                (object) $message = [
                    'content'=> 'You Can Backup One More Time From 5:30pm to 10:30pm',
                    'enable'=> false
                ];
            }
        }else if (DateTime::createFromFormat('h:i a', Carbon::now()->format('h:i a')) > DateTime::createFromFormat('h:i a', "05:30 pm") && DateTime::createFromFormat('h:i a', Carbon::now()->format('h:i a')) < DateTime::createFromFormat('h:i a', "11:30 pm")) {
            if ($today_data_count == 0){
                (object) $message = [
                    'content'=> 'You Missed your first backup today.  1 backup remaining From 5:30pm to 10:30pm',
                    'enable'=> true
                ];
            }
            if ($today_data_count == 1){
                (object) $message = [
                    'content'=> 'You Cannot Backup Today Any More. Thank You',
                    'enable'=> false
                ];
            }
        }
//        if ($today_data_count>0){
////            $time_data = $backups[Carbon::now()->format('Y-m-d')];
////            $current_time = Carbon::parse(key($time_data))->format('h:i a');
//            if ($today_data_count == 1){
//                (object) $message = [
//                    'content'=> 'You Can Backup One More Time From 5:30pm to 10:30pm',
//                    'enable'=> true
//                ];
//            }
//            if ($today_data_count == 2){
//                (object) $message = [
//                    'content'=> 'You Cannot Backup Today Any More. Thank You',
//                    'enable'=> false
//                ];
//            }
////            $start1 = "08:00 am";
////            $end1 = "11:59 am";
////            $start2 = "05:30 pm";
////            $end2 = "10:30 pm";
////            $date1 = DateTime::createFromFormat('h:i a', $current_time);
////            $date2 = DateTime::createFromFormat('h:i a', $start1);
////            $date3 = DateTime::createFromFormat('h:i a', $end1);
////            if ($date1 > $date2 && $date1 < $date3)
////            {
////                echo 'here';
////            }
////            return 'done';
////            next($time_data);
////            return key($time_data);
//        }


        krsort($backups);
        return view('backup.create', compact('backups','message'));
    }

    public function store(Request $request)
    {
        set_time_limit(6000);
        ini_set("pcre.backtrack_limit", "100000000");
        Artisan::call('backup:run --only-db');
        $date = Carbon::now()->format('Y-m-d');
        $time = Carbon::now()->format('H:i:s');
        $this->database
            ->getReference('backup/' . $date . '/' . $time)
            ->set([
                'date_time' => Carbon::now(),
                'date' => $date,
                'time' => $time,
                'backup_done_by' => $request->backed_by,
                'backup_done_ip' => $request->ip(),
                'backup_done_from' => $request->userAgent(),
                'message' => 'backed up successful'
            ]);

        return back()->with('message', 'Backed Up Successfully');
    }
}
