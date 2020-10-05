<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 9:21 AM
 */

namespace KgBot\RackbeatDashboard\Classes;


use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use KgBot\RackbeatDashboard\Mail\JobFailed;
use KgBot\RackbeatDashboard\Models\Job;
use Throwable;

class DashboardJob implements ShouldQueue, Reportable, Executable
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected $jobModel;

	public function __construct( Job $jobModel ) {
		$this->jobModel = $jobModel;
	}

	public function handle() {
		$this->registerContext();
		$this->beforeStart();
		try {
			app()->call( [ $this, 'execute' ] );
			$this->onFinish();
		} catch ( Throwable $e ) {
			$this->onFail( $e );
		} finally {
			$this->detachContext();
		}

	}

	protected function registerContext() {
		\Config::set( 'rackbeat-integration-dashboard.context', $this->context() );
	}

	protected function context() {
		return $this->jobModel;
	}

	protected function beforeStart() {
		$this->jobModel->activate();
	}

	protected function onFinish() {
		$this->jobModel->finish( true );
	}

	protected function onFail( Throwable $e ) {

		$this->sendMailsOnFail( $e );

		if ( $this instanceof Reportable ) {
			$this->jobModel->report = collect( [ 'error' => $e->getMessage() ] );
		}
		if ( $this->isWillBeRetry( $e ) ) {
			$this->jobModel->retryAfterFail();
		} else {
			$this->jobModel->finish( false );
		}
		$this->jobModel->log( $e->getMessage(), 'error', [ 'code' => $e->getCode(), 'file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace() ] );
		$this->fail( $e );
	}

	protected function sendMailsOnFail( Throwable $e ) {

		if ( $this->jobModel->owner !== null && \Config::get( 'rackbeat-integration-dashboard.emails.send_on_fail', true ) === true ) {

			$send     = false;
			$days     = \Config::get( 'rackbeat-integration-dashboard.emails.days', [] );
			$hours    = \Config::get( 'rackbeat-integration-dashboard.emails.hours', [] );
			$datetime = Carbon::now();

			if ( in_array( $datetime->englishDayOfWeek, $days ) && in_array( $datetime->hour, $hours ) ) {

				$send = true;
			}

			if ( $send ) {

				$emails = \Config::get( 'rackbeat-integration-dashboard.emails.addresses', [] );

				if ( count( array_slice( $emails, 1 ) ) > 0 ) {

					$cc = implode( ',', array_slice( $emails, 1 ) );
				} else {

					$cc = '';
				}

				if ( count( $emails ) > 0 ) {
					if ( $cc === '' ) {

						\Mail::to( $emails[0] )
						     ->queue( new JobFailed(
								     $this->jobModel->owner->rackbeat_user_account_id,
								     $this->jobModel->owner->id,
								     $e->getMessage(),
								     Carbon::now()->toDateString(),
								     $this->jobModel->id,
								     $this->jobModel->owner->rackbeat_company_name ?? '',
								     $e,
								     $this->jobModel->command
							     )
						     );
					} else {

						\Mail::to( $emails[0] )
						     ->cc( $cc )
						     ->queue( new JobFailed(
								     $this->jobModel->owner->rackbeat_user_account_id,
								     $this->jobModel->owner->id,
								     $e->getMessage(),
								     Carbon::now()->toDateString(),
								     $this->jobModel->id,
								     $this->jobModel->owner->rackbeat_company_name ?? '',
								     $e,
								     $this->jobModel->command
							     )
						     );
					}
				}
			}
		}
	}

	protected function isWillBeRetry( Throwable $e ): bool {
		return $this->shouldRetryException( $e ) && ( $this->job->maxTries() === null
		                                              || ( $this->attempts() < $this->job->maxTries() && $this->jobModel->attempts <= $this->job->maxTries() ) );
	}

	/**
	 * Check exception message and if we have defined regex pattern for it don't retry job
	 *
	 * @param Throwable $throwable
	 *
	 * @return bool
	 */
	protected function shouldRetryException( Throwable $throwable ) {
		$patterns = Config::get( 'rackbeat-integration-dashboard.exceptions.patterns', [] );
		if ( count( $patterns ) ) {
			foreach ( $patterns as $pattern ) {
				if ( $pattern !== '' ) {
					try {
						if ( preg_match( $pattern, $throwable->getMessage() ) ) {
							return false;
						}
					} catch ( \Exception $exception ) {
						// Do nothing because it probably failed because of invalid pattern
					}
				}
			}
		}

		return true;
	}

	protected function detachContext() {
		\Config::set( 'rackbeat-integration-dashboard.context', null );
	}

	public function execute() {

		throw new Exception( 'You must override execute() method in your own job class' );
	}

	protected function isRetried(): bool {
		return $this->jobModel->attempts > 1;
	}

}