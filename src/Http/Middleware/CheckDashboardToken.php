<?php

namespace KgBot\RackbeatDashboard\Http\Middleware;

use Closure;

class CheckDashboardToken
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle( $request, Closure $next ) {
        if (\Config::get('rackbeat-integration-dashboard.oauth_token', null) !== null && $request->hasHeader('X-Dashboard-Token') && $request->header('X-Dashboard-Token') === \Config::get('rackbeat-integration-dashboard.oauth_token')) {

			return $next( $request );
		}

		return abort( 403, 'Request is not authorized.' );
	}
}
