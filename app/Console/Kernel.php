<?php

namespace App\Console;

use App\Models\UserInfo;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->call(function () {
            $data = UserInfo::select('uid','vipotype','vipendtime')->where('is_visible',1)->get();
            if($data){
                $data = $data->toArray();
                foreach($data as $value){
                    if( $value['vipendtime']>strtotime(date('Y-m-d H:i:s')) ){

                        if($value['vipotype'] == 1){
                            DB::table('user_info')->where('uid',$value['uid'])->update(array('downcount'=>5));
                        }elseif($value['vipotype']==5){
                            DB::table('user_info')->where('uid',$value['uid'])->update(array('downcount'=>8));
                        }elseif($value['vipotype']==10){
                            DB::table('user_info')->where('uid',$value['uid'])->update(array('downcount'=>12));
                        }

                    }
                }
            }
        })->monthly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
