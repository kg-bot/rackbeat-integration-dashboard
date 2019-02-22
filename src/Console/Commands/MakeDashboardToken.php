<?php

namespace KgBot\RackbeatDashboard\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Str;

class MakeDashboardToken extends Command
{
	use ConfirmableTrait;

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'make:dashboard-token {--force : Force the operation to run when in production}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new dashboard key.';


	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle() {
		$key = $this->generateRandomKey();

		// Next, we will replace the application key in the environment file so it is
		// automatically setup for this developer. This key gets generated using a
		// secure random byte generator and is later base64 encoded for storage.
		if ( ! $this->setKeyInEnvironmentFile( $key ) ) {
			return;
		}

		$this->laravel['config']['rackbeat-integration-dashboard.oauth_token'] = $key;

		$this->info( 'Dashboard key set successfully.' );
	}

	/**
	 * Generate a random key for the application.
	 *
	 * @return string
	 */
	protected function generateRandomKey() {
		return Str::random( 255 );
	}

	/**
	 * Set the application key in the environment file.
	 *
	 * @param  string $key
	 *
	 * @return bool
	 */
	protected function setKeyInEnvironmentFile( $key ) {
		$currentKey = $this->laravel['config']['rackbeat-integration-dashboard.oauth_token'];

		if ( strlen( $currentKey ) !== 0 && ( ! $this->confirmToProceed() ) ) {
			return false;
		}

		$this->writeNewEnvironmentFileWith( $key );

		return true;
	}

	/**
	 * Write a new environment file with the given key.
	 *
	 * @param  string $key
	 *
	 * @return void
	 */
	protected function writeNewEnvironmentFileWith( $key ) {
		file_put_contents( $this->laravel->environmentFilePath(), preg_replace(
			$this->keyReplacementPattern(),
			'RACKBEAT_DASHBOARD_TOKEN=' . $key,
			file_get_contents( $this->laravel->environmentFilePath() )
		) );
	}

	/**
	 * Get a regex pattern that will match env APP_KEY with any random key.
	 *
	 * @return string
	 */
	protected function keyReplacementPattern() {
		$escaped = '=' . $this->laravel['config']['rackbeat-integration-dashboard.oauth_token'];

		return "/^RACKBEAT_DASHBOARD_TOKEN{$escaped}/m";
	}
}

