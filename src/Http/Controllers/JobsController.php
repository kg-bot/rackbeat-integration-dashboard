<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/21/19
 * Time: 6:03 PM
 */

namespace KgBot\RackbeatDashboard\Http\Controllers;


use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use KgBot\RackbeatDashboard\Models\Job;

class JobsController extends Controller
{
	/**
	 * @param Request $request
	 * @param int     $rackbeat_account_id
	 *
	 * @return JsonResponse
	 */
	public function index( Request $request, $rackbeat_account_id ) {
		try {
			/** @var Builder $query */
			$query = Job::OfRackbeatAccount( $rackbeat_account_id );

			if ( $request->filled( 'state' ) ) {
				$query->where( 'state', $request->get( 'state' ) );
			}

			if ( $request->filled( 'created_at' ) ) {
				$query->whereDate( 'created_at', '>=', Carbon::parse( ( $request->get( 'created_at' ) ) ) );
			}

			if ( $request->filled( 'type' ) ) {
				$query->where( 'command', 'like', '%' . $request->get( 'type' ) );
			}

			if ( $request->filled( 'search' ) ) {
				$query->where( function ( $query ) use ( $request ) {
					$query->where( 'title', 'like', '%' . $request->get( 'search' ) . '%' )
					      ->orWhere( 'args', 'like', '%' . $request->get( 'search' ) . '%' );
				} );
			}


			$jobs = $query->latest()->paginate( $request->get( 'per_page', 10 ), [ '*' ], 'page', $request->get( 'page', 1 ) );

			return response()->json( compact( 'jobs' ) );

		} catch ( Exception $exception ) {

			return response()->json( [ 'error' => $exception->getMessage() ], 500 );
		}
	}

	public function details( Request $request, Job $job ) {

		return response()->json( compact( 'job' ) );
	}

	public function logs( Request $request, Job $job ) {
		$logs = $job->logs()->paginate( 10, '*', 'page', $request->get( 'page' ) );

		return response()->json( compact( 'logs' ) );
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

			$job->retry( true );

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

	public function filterableTypes() {
		$dir   = File::allFiles( app_path( 'Jobs' ) );
		$types = collect();

		foreach ( $dir as $file ) {
			if ( $file->getExtension() === 'php' ) {
				$types->push( $file->getBasename( '.php' ) );
			}
		}

		return response()->json( compact( 'types' ) );
	}
}