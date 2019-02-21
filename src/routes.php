<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/21/19
 * Time: 6:13 PM
 */

Route::group( [ 'prefix' => 'rackbeat-integration-dashboard', 'namespace' => 'KgBot\RackbeatDashboard\Http\Controllers' ], function () {

	Route::get( 'failed-jobs', 'FailedJobsController@index' )->name( 'failed-jobs.index' );
} );