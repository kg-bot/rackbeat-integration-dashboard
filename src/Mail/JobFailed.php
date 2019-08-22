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
	public $error_message;
	public $failed_at;
	public $job_id;
    public $plugin_name;
    public $rackbeat_company_name;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
    public function __construct($rackbeat_user_account_id, $connection_id, $message, $failed_at, $job_id, $rackbeat_company_name = '')
    {

        $this->rackbeat_user_account_id = $rackbeat_user_account_id;
		$this->connection_id    = $connection_id;
		$this->error_message    = $message;
		$this->failed_at        = $failed_at;
		$this->job_id           = $job_id;
        $this->plugin_name = config('app.plugin_name', '');
        $this->rackbeat_company_name = $rackbeat_company_name;
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
