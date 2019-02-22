<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 3:14 PM
 */

namespace KgBot\RackbeatDashboard\Notifications;


use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class StateMessage extends Notification
{
	private $state;

	public function __construct( string $state ) {
		$this->state = $state;
	}

	public function via( $notifiable ) {
		return [ 'broadcast' ];
	}

	public function toBroadcast( $notifiable ) {
		return ( new BroadcastMessage( $this->toArray( $notifiable ) ) )->onQueue( 'notify' )->delay( 0 );
	}

	public function toArray( $notifiable ) {
		return [
			'message_type' => Notificator::EVENT_STATE,
			'message'      => $this->state,
			'job'          => $notifiable->toJson(),
			'id'           => $notifiable->id,
		];
	}

	public function broadcastAs() {
		return 'jobState';
	}
}