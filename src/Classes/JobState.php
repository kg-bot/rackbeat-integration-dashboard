<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 9:24 AM
 */

namespace KgBot\RackbeatDashboard\Classes;

use Illuminate\Notifications\Notification;
use InvalidArgumentException;
use KgBot\RackbeatDashboard\Notifications\Notificator;
use KgBot\RackbeatDashboard\Notifications\UserMessage;

class JobState
{
	const PENDING    = 'pending';
	const PROCESSING = 'processing';
	const SUCCESS    = 'success';
	const FAIL       = 'fail';
	const RETRY      = 'retry';
	const DECLINED   = 'declined';

	protected static $variants = [
		self::PENDING    => 'pending',
		self::FAIL       => 'fail',
		self::PROCESSING => 'processing',
		self::SUCCESS    => 'success',
		self::RETRY      => 'retry',
		self::DECLINED   => 'retry',
	];

	protected static $labels = [
		self::PENDING    => '<div class="badge badge-light">PENDING</div>',
		self::FAIL       => '<div class="badge badge-danger">FAIL</div>',
		self::PROCESSING => '<div class="badge badge-info">PROCESSING</div>',
		self::SUCCESS    => '<div class="badge badge-success">SUCCESS</div>',
		self::RETRY      => '<div class="badge badge-dark">RETRY</div>',
		self::DECLINED   => '<div class="badge badge-warning">DECLINED</div>',
	];

	protected static $notices = [
		self::FAIL       => [ Notificator::TYPE_ERROR, 'Job Execution {job} failed' ],
		self::PROCESSING => [ Notificator::TYPE_INFO, 'Job Execution {job} started' ],
		self::SUCCESS    => [ Notificator::TYPE_SUCCESS, 'Job Execution {job} finished' ],
		self::RETRY      => [ Notificator::TYPE_WARNING, 'Job Execution {job} failed, job will be restarted' ],
	];

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @param string $value
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct( string $value ) {
		if ( ! isset( self::$variants[ $value ] ) ) {
			throw new InvalidArgumentException();
		}
		$this->value = $value;
	}

	public function value() {
		return $this->value;
	}

	public function name() {
		return self::$variants[ $this->value ];
	}

	public function label() {
		return self::$labels[ $this->value ];
	}

	public function isPending(): bool {
		return $this->value == self::PENDING;
	}

	public function isActive(): bool {
		return $this->value == self::PROCESSING;
	}

	public function isSuccess(): bool {
		return $this->value == self::SUCCESS;
	}

	public function isWaitRetry(): bool {
		return $this->value == self::RETRY;
	}

	public function isFinished(): bool {
		return in_array( $this->value, [ self::SUCCESS, self::FAIL ] );
	}

	public function isDeclined(): bool {
		return $this->value == self::DECLINED;
	}

	public function __toString() {
		return $this->value;
	}

	public function notice( $job = '' ): Notification {
		[ $type, $message ] = self::$notices[ $this->value ];
		$message = strtr( $message, [ 'job' => $job ] );

		return new UserMessage( $type, $message );
	}
}