<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/21/19
 * Time: 6:13 PM
 */

use KgBot\RackbeatDashboard\Http\Middleware\CheckDashboardToken;

Route::group( [ 'prefix' => 'rackbeat-integration-dashboard', 'namespace' => 'KgBot\RackbeatDashboard\Http\Controllers', 'middleware' => [ 'web', CheckDashboardToken::class ] ], function () {

	Route::get( 'jobs/{connection_id}', 'JobsController@index' )->name( 'dashboard-jobs.index' );

	Route::get( 'retry/{job}', 'JobsController@retry' )->name( 'dashboard-jobs.retry' );
} );