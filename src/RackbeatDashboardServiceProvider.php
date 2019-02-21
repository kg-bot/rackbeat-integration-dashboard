<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/21/19
 * Time: 5:28 PM
 */

namespace KgBot\RackbeatDashboard;


use Illuminate\Support\ServiceProvider;

class RackbeatDashboardServiceProvider extends ServiceProvider
{

	/**
	 * Boot.
	 */
	public function boot() {

		$configPath = __DIR__ . '/config/rackbeat-integration-dashboard.php';

		$this->mergeConfigFrom( $configPath, 'rackbeat-integration-dashboard' );

		$configPath = __DIR__ . '/config/rackbeat-integration-dashboard.php';

		if ( function_exists( 'config_path' ) ) {

			$publishPath = config_path( 'rackbeat-integration-dashboard.php' );

		} else {

			$publishPath = base_path( 'config/rackbeat-integration-dashboard.php' );

		}

		$this->publishes( [ $configPath => $publishPath ], 'config' );

		$this->loadRoutesFrom( '../routes.php' );
	}

	public function register() {

	}
}