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
			'stn@rackbeat.com'
		],
		'send_on_fail' => env( 'RACKBEAT_DASHBOARD_SEND_EMAIL_ON_FAIL', true ),
	]
];