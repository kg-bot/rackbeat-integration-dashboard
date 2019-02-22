<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 8:53 AM
 */

namespace KgBot\RackbeatDashboard\Classes;

use Monolog\Logger;

class JobLogger
{
	/**
	 * @return Logger
	 */
	public function __invoke() {
		$monolog = new Logger( 'job_logger' );
		$level   = config( 'logging.channels.custom.level', 'debug' );
		$handler = new JobLogDbHandler( $level );
		$monolog->pushHandler( $handler );

		return $monolog;
	}
}