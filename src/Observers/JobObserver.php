<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 10:37 AM
 */

namespace KgBot\RackbeatDashboard\Observers;


use Illuminate\Support\Facades\Config;
use KgBot\RackbeatDashboard\Models\Job;

class JobObserver
{

	/**
	 * @param Job $job
	 */
	public function creating( Job $job ) {
		$job->fillDefaults();
	}

	/**
	 * @param Job $job
	 *
	 * @throws \Exception
	 */
	public function created( Job $job ) {
		try {

			dispatch( new $job->command( $job, ...$job->args ) )->onQueue( $job->queue )->onConnection( Config::get( 'queue.default', 'redis' ) );
		} catch ( \Throwable $e ) {
			$job->delete();
		}
	}
}