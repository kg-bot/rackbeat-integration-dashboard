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
use KgBot\RackbeatDashboard\Models\Job;

class UserMessage extends Notification
{

	private $type;

	private $message;

	public function __construct( string $type, string $message ) {
		$this->type    = $type;
		$this->message = $message;
	}

	public function via( $notifiable ) {
		return [ 'broadcast' ];
	}

	public function toBroadcast( $notifiable ) {
		return ( new BroadcastMessage( $this->toArray( $notifiable ) ) )->onQueue( 'notify' )->delay( 0 );
	}

	public function toArray( $notifiable ) {
		if ( $notifiable instanceof Job ) {
			$this->message = sprintf( '[%s:%s] %s', $notifiable->command, $notifiable->id, $this->message );
		}

		return [
			'message_type' => $this->type,
			'message'      => $this->message,
		];
	}

	public function broadcastAs() {
		return 'UserNotice';
	}
}