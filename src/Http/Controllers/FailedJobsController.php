<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/21/19
 * Time: 6:03 PM
 */

namespace KgBot\RackbeatDashboard\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FailedJobsController extends Controller
{
	/**
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index( Request $request ) {
		try {
			$failed = DB::raw( 'SELECT * FROM failed_jobs ORDER BY failed_at DESC' );

			return response()->json( compact( 'failed' ) );

		} catch ( \Exception $exception ) {

			return response()->json( [ 'error' => $exception->getMessage() ], 500 );
		}
	}
}