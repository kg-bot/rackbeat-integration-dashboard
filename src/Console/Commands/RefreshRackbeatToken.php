<?php

namespace KgBot\RackbeatDashboard\Console\Commands;

use Illuminate\Console\Command;

class RefreshRackbeatToken extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'refresh-rackbeat-token';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

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
	 * @return mixed
	 */
	public function handle() {
		$connections = $this->laravel['config']['rackbeat-integration-dashboard.connection_class']::Active()->get();

		foreach ( $connections as $connection ) {
			if ( method_exists( $connections, 'refreshRackbeatToken' ) ) {
				try {
					$connection->refreshRackbeatToken();
				} catch ( \Exception $exception ) {
					\Log::error( 'Can\'t refresh RB token, connection ' . $connection->id . ', because: ' . $exception->getMessage() );

					if ( method_exists( $connection, 'logError' ) ) {
						$connection->logError( 'Can\'t refresh Rackbeat token, error: ' . $exception->getMessage() );
					}
				}
			}
		}
	}
}
