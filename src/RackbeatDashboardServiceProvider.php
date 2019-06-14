<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/21/19
 * Time: 5:28 PM
 */

namespace KgBot\RackbeatDashboard;


use Illuminate\Support\ServiceProvider;
use KgBot\RackbeatDashboard\Console\Commands\MakeDashboardToken;
use KgBot\RackbeatDashboard\Models\Job;
use KgBot\RackbeatDashboard\Observers\JobObserver;

class RackbeatDashboardServiceProvider extends ServiceProvider
{

	/**
	 * Boot.
	 */
	public function boot() {

		/**
		 * Default package config registration
		 */
		$configPath = __DIR__ . '/config/rackbeat-integration-dashboard.php';

		$this->mergeConfigFrom( $configPath, 'rackbeat-integration-dashboard' );

		$configPath = __DIR__ . '/config/rackbeat-integration-dashboard.php';

		if ( function_exists( 'config_path' ) ) {

			$publishPath = config_path( 'rackbeat-integration-dashboard.php' );

		} else {

			$publishPath = base_path( 'config/rackbeat-integration-dashboard.php' );

		}

		$this->publishes( [ $configPath => $publishPath ], 'config' );

		$this->loadRoutesFrom( __DIR__ . '/routes.php' );

		$this->loadMigrationsFrom( __DIR__ . '/database/migrations' );

		$this->loadViewsFrom( __DIR__ . '/resources/views', 'rackbeat-dashboard' );

		if ( $this->app->runningInConsole() ) {
			$this->commands( [
				MakeDashboardToken::class,
			] );
		}

		Job::observe( JobObserver::class );
	}

	public function register() { }
}