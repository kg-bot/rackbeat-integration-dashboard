<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 9:23 AM
 */

namespace KgBot\RackbeatDashboard\Models;

use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use KgBot\RackbeatDashboard\Classes\JobState;
use KgBot\RackbeatDashboard\Notifications\LogMessage;
use KgBot\RackbeatDashboard\Notifications\Progress;
use KgBot\RackbeatDashboard\Notifications\StateMessage;

/**
 * @property int                 $id
 * @property string              $queue
 * @property string              $command
 * @property array|null          $payload
 * @property array|null          $report
 * @property string              $state
 * @property int|null            $progress
 * @property int                 $attempts
 * @property int                 $created_by
 * @property Carbon\Carbon       $created_at
 * @property Carbon\Carbon       $finished_at
 * @property Model               $owner
 * @property JobLog[]|Collection $logs
 */
class Job extends Model
{

	use Notifiable;

	const UPDATED_AT = null;
	const CREATED_AT = 'created_at';
	public    $timestamps = true;
	protected $primaryKey = 'id';
	protected $fillable   = [ 'command', 'queue', 'payload', 'created_by', 'args', 'title', 'delay', 'attempts' ];

	protected $casts = [
		'payload'     => 'array',
		'report'      => 'array',
		'finished_at' => 'datetime',
		'args'        => 'array',
	];

	protected $table = 'dashboard_jobs';


	public function logs() {
		return $this->hasMany( JobLog::class, 'job_id', 'id' );
	}

	/**
	 * @return BelongsTo
	 */
	public function owner() {
		return $this->belongsTo( \Config::get( 'rackbeat-integration-dashboard.connection_class', '\App\Connection' ), 'created_by' );
	}

	public function scopeNotFinished( Builder $query ) {
		return $query->whereNotIn( 'state', [ 'fail', 'success' ] );
	}

	public function scopeFailed( Builder $query ) {

		return $query->whereState( JobState::FAIL );
	}

	public function scopeOfRackbeatAccount( Builder $query, $account_id ) {
		return $query->whereHas( 'owner', function ( $query ) use ( $account_id ) {

			return $query->where( 'rackbeat_user_account_id', $account_id );
		} );
	}

	/**
	 * @param int $account_id
	 *
	 * @return bool
	 */
	public function belongsToAccount( int $account_id ): bool {
		return ! empty( $this->owner->rackbeat_user_account_id ) && $this->owner->rackbeat_user_account_id === $account_id;
	}

	public function fillDefaults() {
		$this->progress = 0;
		$this->state    = JobState::PENDING;

		return $this;
	}

	public function setArgsAttribute( $value ) {
		$this->attributes['args'] = json_encode( $value ) ?? [];
	}

	public function getArgsAttribute() {
		return json_decode( $this->attributes['args'] );
	}

	public function updateProgress( int $value, $message = null ) {
		if ( $message && $this->shouldNotify() ) {
			$this->notifyNow( new LogMessage( $message ) );
		}
		$this->progress = $value;
		$this->save();
		if ( $this->shouldNotify() ) {
			$this->notifyNow( new Progress( $value ) );
		}
	}

	public function activate() {
		$this->state = JobState::PROCESSING;
		$this->save();
		$this->stateChanged();
		$this->log( 'Job started', 'debug', [ 'connection' => $this->created_by ] );
	}

	protected function stateChanged() {
		if ( $this->shouldNotify() ) {
			$this->notifyNow( new StateMessage( $this->state ) );
			if ( $this->owner !== null ) {
				$this->owner->notifyNow(
					$this->jobState()->notice( $this->identity() )
				);
			}
		}
	}

	public function jobState(): JobState {
		return new JobState( $this->state ?? JobState::PENDING );
	}

	public function identity() {
		return $this->commandName() . ':' . $this->id;
	}

	public function commandName() {
		return class_basename( $this->command );
	}

	public function retryAfterFail() {
		$this->state    = JobState::RETRY;
		$this->attempts += 1;
		$this->save();
		$this->stateChanged();
		$this->retry( true, Config::get( 'rackbeat-integration-dashboard.retry_after', 1 ) );
	}

	public function finish( bool $success = true ) {
		$this->state       = $success ? JobState::SUCCESS : JobState::FAIL;
		$this->finished_at = Carbon::now();

		if ( $success ) {
			$this->progress = 100;
			$this->log( 'Finished' );
		} else {
			$this->log( 'Failed', 'error' );
		}
		$this->save();

		$this->stateChanged();
	}

	public function retry( $force = false, $after = null ) {
		if ( $force || $this->state === JobState::RETRY ) {

			self::create( [

				'command'    => $this->command,
				'queue'      => $this->queue,
				'args'       => $this->args,
				'title'      => $this->title,
				'created_by' => $this->created_by,
				'delay'      => $after,
				'attempts'   => $this->attempts
			] );

			$this->delete();
		} else {

			throw new Exception( 'This job can\'t be retried because it\'s in ' . $this->state . ' state.' );
		}
	}

	public function receivesBroadcastNotificationsOn() {
		return 'Job.' . $this->id;
	}

	public function shouldNotify() {
		return Config::get( 'rackbeat-integration-dashboard.should_notify' );
	}

	public function log( $message, $level = 'debug', $extra = [] ) {
		try {
			$this->logs()->create( [
				'context' => $this->commandName(),
				'level'   => $level,
				'message' => $message,
				'extra'   => array_merge( [ 'rackbeat_user_account_id' => $this->owner->rackbeat_user_account_id, 'owner' => $this->created_by ], $extra )
			] );
		} catch ( \Exception $exception ) {
			// Can't create log, probably job is already deleted or there is recursion in JSON
		}
	}

	/**
	 * We are not using updated at column so we override this
	 *
	 * @param mixed $value
	 *
	 * @return $this|Job
	 */
	public function setUpdatedAt( $value ) {
		return $this;
	}
}