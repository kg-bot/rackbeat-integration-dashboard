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
use Illuminate\Support\Facades\Mail;
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
		Config::set( 'rackbeat-integration-dashboard.context', $this->context() );
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
		if ( $this->isWillBeRetry() ) {
			$this->jobModel->retryAfterFail();
		} else {
			$this->jobModel->finish( false );
		}
		$this->fail( $e );
	}

	protected function sendMailsOnFail( Throwable $e ) {

		if ( Config::get( 'rackbeat-integration-dashboard.emails.send_on_fail', true ) === true ) {

			$emails = Config::get( 'rackbeat-integration-dashboard.emails.addresses', [] );

			Mail::to( $emails[0] )
			    ->cc( implode( ',', array_slice( $emails, 1 ) ) )
                ->queue(new JobFailed(
                        $this->jobModel->owner->rackbeat_user_account_id,
				    $this->jobModel->owner->id,
				    $e->getMessage(),
				    Carbon::now()->toDateString(),
                        $this->jobModel->id,
                        $this->jobModel->owner->rackbeat_company_name ?? ''
                    )
                );
		}
	}

	protected function isWillBeRetry(): bool {
		return is_null( $this->job->maxTries() )
		       || $this->attempts() < $this->job->maxTries();
	}

	protected function detachContext() {
		Config::set( 'rackbeat-integration-dashboard.context', null );
	}

	public function execute() {

		throw new Exception( 'You must override execute() method in your own job class' );
	}

	protected function isRetried(): bool {
		return $this->jobModel->attempts > 1;
	}

}