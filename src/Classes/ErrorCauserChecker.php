<?php namespace KgBot\RackbeatDashboard\Classes;

use Exception;
use Illuminate\Database\DetectsLostConnections;
use Illuminate\Support\Str;
use Throwable;

class ErrorCauserChecker
{
	use DetectsLostConnections;

	public static function isCausedByLostConnection( Throwable $e ) {
		return ( new static )->causedByLostConnection( $e )
		       || Str::contains( $e->getMessage(), [
				'Error while sending STMT_PREPARE packet',
			] );
	}

	public static function isMessageCausedByLostConnection( $message ) {
		return ( new static )->causedByLostConnection( new Exception( $message ) )
		       || Str::contains( $message, [
				'Error while sending STMT_PREPARE packet',
			] );
	}
}
