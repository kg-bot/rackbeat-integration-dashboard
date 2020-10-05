<?php

namespace KgBot\RackbeatDashboard\Http\Middleware;

use Closure;

class JobBelongsToAccount
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure                 $next
	 *
	 * @return mixed
	 */
	public function handle( $request, Closure $next ) {
		$job = $request->route( 'job' );
		if ( ! $job->belongsToAccount( $request->route( 'rackbeat_account_id' ) ) ) {
			if ( $request->wantsJson() ) {

				return response()->json( 'This job does not belong to this user account', 403 );
			}

			throw new \Exception( 'This job does not belong to this user account', 403 );
		}

		return $next( $request );
	}
}
