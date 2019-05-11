<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/21/19
 * Time: 6:03 PM
 */

namespace KgBot\RackbeatDashboard\Http\Controllers;


use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use KgBot\RackbeatDashboard\Models\Job;

class JobsController extends Controller
{
	/**
	 * @param Request $request
	 *
	 * @return JsonResponse
	 */
	public function index( Request $request, $rackbeat_account_id ) {
		try {
			$jobs = Job::OfRackbeatAccount( $rackbeat_account_id )->latest()->paginate( $request->get( 'per_page', 10 ), [ '*' ], 'page', $request->get( 'page', 1 ) );

			return response()->json( compact( 'jobs' ) );

		} catch ( Exception $exception ) {

			return response()->json( [ 'error' => $exception->getMessage() ], 500 );
		}
	}

	public function details( Request $request, Job $job ) {

		return response()->json( [ 'job' => $job ] );
	}

	/**
	 * Retry failed job which is set as ready for retry
	 *
	 * @param Job $job
	 *
	 * @return JsonResponse
	 */
	public function retry( Job $job ) {
		/**
		 * @var $job Job
		 */
		try {

			$job->retry();

			return response()->json( [ 'message' => 'Job ' . $job->id . ' has been sent to queue.' ] );
		} catch ( Exception $exception ) {

			return response()->json( [ 'message' => $exception->getMessage() ], 500 );
		}
	}

	/**
	 * @param Job $job
	 *
	 * @return ResponseFactory|Response
	 * @throws Exception
	 */
	public function delete( Job $job ) {
		$job->delete();

		return response( 'success' );
	}
}