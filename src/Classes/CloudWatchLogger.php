<?php


namespace KgBot\RackbeatDashboard\Classes;


use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

class CloudWatchLogger
{
	/**
	 * The LogToChannels channels.
	 *
	 * @var Logger[]
	 */
	protected $channels = [];

	/**
	 *
	 * @param string $streamName
	 * @param string $groupName
	 *
	 * @throws \Exception
	 */
	public function __construct( string $streamName, string $groupName ) {
		// Add the logger if it doesn't exist
		if ( ! isset( $this->channels[ $streamName ] ) ) {
			$sdkParams = [
				'region'      => config( 'services.ses.region' ),
				'version'     => 'latest',
				'credentials' => [
					'key'    => config( 'services.ses.key' ),
					'secret' => config( 'services.ses.secret' ),
					'token'  => null,
				]
			];

			// Instantiate AWS SDK CloudWatch Logs Client
			$client = new CloudWatchLogsClient( $sdkParams );

			// Days to keep logs, 14 by default. Set to `null` to allow indefinite retention.
			$retentionDays = config( 'rackbeat-integration-dashboard.logging.cloudwatch.retentionDays' );

			// Instantiate handler (tags are optional)
			$handler = new CloudWatch( $client, $groupName, $streamName, $retentionDays, 10000 );

			// Optionally set the JsonFormatter to be able to access your log messages in a structured way
			$handler->setFormatter( new LineFormatter( null, null, true, true ) );

			// Create a log channel
			$log = new Logger( $groupName );

			// Set handler
			$log->pushHandler( $handler );

			$this->channels[ $streamName ] = $log;
		}
	}

	/**
	 * @param string $streamName
	 * @param int    $level   The error level
	 * @param string $message The error message
	 * @param array  $context Optional context arguments
	 *
	 * @return null
	 */
	public function log( string $streamName, int $level, string $message, array $context = [] ) {
		return $this->channels[ $streamName ]->{Logger::getLevelName( $level )}( $message, $context );
	}

	/**
	 * Adds a log record at the DEBUG level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function debug( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::DEBUG, $message, $context );
	}

	/**
	 * Adds a log record at the INFO level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function info( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::INFO, $message, $context );
	}

	/**
	 * Adds a log record at the NOTICE level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function notice( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::NOTICE, $message, $context );
	}

	/**
	 * Adds a log record at the WARNING level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function warn( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::WARNING, $message, $context );
	}

	/**
	 * Adds a log record at the WARNING level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function warning( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::WARNING, $message, $context );
	}

	/**
	 * Adds a log record at the ERROR level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function err( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::ERROR, $message, $context );
	}

	/**
	 * Adds a log record at the ERROR level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function error( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::ERROR, $message, $context );
	}

	/**
	 * Adds a log record at the CRITICAL level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function crit( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::CRITICAL, $message, $context );
	}

	/**
	 * Adds a log record at the CRITICAL level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function critical( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::CRITICAL, $message, $context );
	}

	/**
	 * Adds a log record at the ALERT level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function alert( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::ALERT, $message, $context );
	}

	/**
	 * Adds a log record at the EMERGENCY level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function emerg( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::EMERGENCY, $message, $context );
	}

	/**
	 * Adds a log record at the EMERGENCY level.
	 *
	 * @param string $channel The channel name
	 * @param string $message The log message
	 * @param array  $context The log context
	 *
	 * @return null
	 * @throws \Exception
	 */
	public function emergency( string $channel, string $message, array $context = [] ) {
		return $this->log( $channel, Logger::EMERGENCY, $message, $context );
	}
}