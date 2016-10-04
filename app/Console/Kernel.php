<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

use Redis;

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
        $schedule->call( function(){
		$views = Redis::keys( '*:views' );
		$results = [];
		Redis::multi();
		foreach( $views as $v )
		{
			Redis::smembers( $v );
		}
		$results = Redis::exec();
		echo json_encode($results);
	})->everyMinute()
	->sendOutputTo( 'output.txt' );
    }
}
