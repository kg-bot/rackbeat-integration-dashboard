<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/21/19
 * Time: 6:03 PM
 */

namespace KgBot\RackbeatDashboard\Http\Controllers;


use Illuminate\Http\Request;
use KgBot\RackbeatDashboard\Models\Job;

class JobsController extends Controller
{
	/**
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index( Request $request, $rackbeat_account_id ) {
		try {
			$jobs = Job::OfRackbeatAccount( $rackbeat_account_id )->latest()->paginate( $request->get( 'per_page', 10 ), '*', 'page', $request->get( 'page', 1 ) );

			return response()->json( compact( 'jobs' ) );

		} catch ( \Exception $exception ) {

			return response()->json( [ 'error' => $exception->getMessage() ], 500 );
		}
	}

	/**
	 * Retry failed job which is set as ready for retry
	 *
	 * @param Job $job
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function retry( Job $job ) {
		/**
		 * @var $job Job
		 */
		try {

			$job->retry();

			return response()->json( [ 'message' => 'Job ' . $job->id . ' has been sent to queue.' ] );
		} catch ( \Exception $exception ) {

			return response()->json( [ 'message' => $exception->getMessage() ], 500 );
		}
	}

	/**
	 * @param Job $job
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws \Exception
	 */
	public function delete( Job $job ) {
		$job->delete();

		return response( 'success' );
	}
}