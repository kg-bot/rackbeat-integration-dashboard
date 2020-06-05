<?php
/**
 * Created by PhpStorm.
 * User: nts
 * Date: 31.3.18.
 * Time: 15.34
 */

use App\Connection;

return [

	'context'          => null,
	'connection_class' => Connection::class,
	'oauth_token'      => env( 'RACKBEAT_DASHBOARD_TOKEN' ),

    'emails' => [

	    'addresses'    => [

		    'ninicstefan94@gmail.com',
		    'stn@rackbeat.com',
	    ],
	    'send_on_fail' => env( 'RACKBEAT_DASHBOARD_SEND_EMAIL_ON_FAIL', true ),
	    'days'         => [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ],
	    'hours'        => [ 8, 9, 10, 11, 12, 13, 14, 15, 16, 17 ],
    ],
	'should_notify' => env( 'RACKBEAT_DASHBOARD_SHOULD_NOTIFY', false ),
	'retry_after' => env( 'RACKBEAT_DASHBOARD_RETRY_AFTER', 1 ),
];