<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 3:13 PM
 */

namespace KgBot\RackbeatDashboard\Notifications;


use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class LogMessage extends Notification
{
	private $message;

	public function __construct( string $message ) {
		$this->message = $message;
	}

	public function via( $notifiable ) {
		return [ 'broadcast' ];
	}

	public function toBroadcast( $notifiable ) {
		return ( new BroadcastMessage( $this->toArray( $notifiable ) ) )->onQueue( 'notify' )->delay( 0 );
	}

	public function toArray( $notifiable ) {
		return [
			'message_type' => Notificator::EVENT_LOG_MESSAGE,
			'message'      => $this->message,
			'id'           => $notifiable->id,
		];
	}

	public function broadcastAs() {
		return 'jobMessage';
	}
}