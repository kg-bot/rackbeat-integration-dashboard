<?php

namespace KgBot\RackbeatDashboard\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobFailed extends Mailable
{
	use Queueable, SerializesModels;

	public $rackbeat_user_account_id;
	public $connection_id;
	public $message;
	public $failed_at;
	public $job_id;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct( $rackbeat_user_account_id, $connection_id, $message, $failed_at, $job_id ) {

		$this->rackbeat_user_id = $rackbeat_user_account_id;
		$this->connection_id    = $connection_id;
		$this->message          = $message;
		$this->failed_at        = $failed_at;
		$this->job_id           = $job_id;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build() {
		return $this->view( 'rackbeat-dashboard::mail.job-failed' );
	}
}
