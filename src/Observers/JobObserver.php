<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 10:37 AM
 */

namespace KgBot\RackbeatDashboard\Observers;


use Carbon\Carbon;
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

            $delay = ($job->delay !== null) ? Carbon::now()->addMinutes($job->delay) : 0;

            dispatch(new $job->command($job, ...$job->args))->onQueue($job->queue)->onConnection(\Config::get('queue.default', 'redis'))->delay($delay);
		} catch ( \Throwable $e ) {
			\Log::error( 'Can\'t dispatch job ' . $job->command ?? $job->id . ', error: ' . $e->getMessage() );
			$job->delete();
		}
	}
}