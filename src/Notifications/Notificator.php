<?php
/**
 * Created by PhpStorm.
 * User: kgbot
 * Date: 2/22/19
 * Time: 3:13 PM
 */

namespace KgBot\RackbeatDashboard\Notifications;


class Notificator
{
	const TYPE_INFO    = 'info';
	const TYPE_ERROR   = 'danger';
	const TYPE_SUCCESS = 'success';
	const TYPE_DEFAULT = 'default';
	const TYPE_WARNING = 'warning';

	const EVENT_PROGRESS    = 'progress';
	const EVENT_LOG_MESSAGE = 'log_message';
	const EVENT_STATE       = 'state';
}