<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 8:51 AM
 */

namespace KgBot\RackbeatDashboard\Classes;

use KgBot\RackbeatDashboard\Models\Job;
use Monolog\Handler\AbstractProcessingHandler;

class JobLogDbHandler extends AbstractProcessingHandler
{
	/**
	 * @param array $record
	 */
	protected function write( array $record ) {
        $jobContext = \Config::get('rackbeat-integration-dashboard.context');

		if ( $jobContext instanceof Job ) {
            \DB::table('job_logs')->insert([
				'loggable_id'   => $jobContext->id,
				'loggable_type' => $jobContext->commandName(),
				'message'       => $record['message'],
				'level'         => $record['level_name'],
				'context'       => json_encode( $record['context'] ?? [] ),
				'extra'         => json_encode( $record['extra'] ?? [] ),
				'created_at'    => $record['datetime']->format( 'Y-m-d H:i:s' ),
			] );
		}
	}
}