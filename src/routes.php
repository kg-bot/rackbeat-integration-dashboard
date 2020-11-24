<?php

use KgBot\RackbeatDashboard\Http\Middleware\CheckDashboardToken;
use KgBot\RackbeatDashboard\Http\Middleware\JobBelongsToAccount;

Route::group( [ 'prefix' => 'rackbeat-integration-dashboard', 'namespace' => 'KgBot\RackbeatDashboard\Http\Controllers', 'middleware' => [ 'web', CheckDashboardToken::class ] ], function () {

	Route::get( 'jobs/{rackbeat_account_id}', 'JobsController@index' )->name( 'dashboard-jobs.index' );
	Route::get( 'job-types', 'JobsController@filterableTypes' )->name( 'dashboard-jobs.types' );

	Route::group( [ 'middleware' => [ JobBelongsToAccount::class ] ], function () {
		Route::post( 'retry/{job}/{rackbeat_account_id}', 'JobsController@retry' )->name( 'dashboard-jobs.retry' );

		Route::delete( 'jobs/{job}/{rackbeat_account_id}', 'JobsController@delete' )->name( 'dashboard-jobs.delete' );

		Route::get( 'job/{job}/logs/{rackbeat_account_id}', 'JobsController@logs' )->name( 'dashboard-jobs.logs' );

		Route::get( 'job/{job}/{rackbeat_account_id}', 'JobsController@details' )->name( 'dashboard-jobs.details' );
	} );
} );