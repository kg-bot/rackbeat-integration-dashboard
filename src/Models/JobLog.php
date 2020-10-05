<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 9:28 AM
 */

namespace KgBot\RackbeatDashboard\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int                        $id
 * @property int                        $loggable_id
 * @property string                     $loggable_type
 * @property string                     $level
 * @property string                     $message
 * @property string                     $context
 * @property string                     $extra
 * @property \Illuminate\Support\Carbon $created_at
 * @method static |Builder groups()
 * @method static |Builder job( JobLog $jobLog )
 */
class JobLog extends Model
{
	const        UPDATED_AT   = null;
	public const ERROR_LEVELS = [ 'error', 'failed', 'fail', 'warning' ];

	public $timestamps = true;

	protected $table    = 'job_logs';
	protected $fillable = [ 'message', 'context', 'extra', 'level' ];

	protected $casts = [
		'extra' => 'array'
	];

	public function jobs() {
		return $this->belongsToMany( Job::class );
	}

	public function scopeForRackbeatAccount( Builder $builder, int $accountId ) {
		return $builder->where( 'extra->rackbeat_user_account_id', $accountId );
	}

	public function scopeError( Builder $builder ) {
		return $builder->whereIn( 'level', self::ERROR_LEVELS );
	}
}