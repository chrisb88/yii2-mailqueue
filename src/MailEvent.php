<?php
/**
 * MailEvent
 *
 * PHP Version 5.4.17
 *
 * @author chris <chris@chrisboehme.de>
 * @copyright 2015 chris---
 */

namespace cbm\mailqueue;

use yii\base\Event;

/**
 * MailEvent represents the event parameter used for events triggered by [[QueueMailer]].
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 */
class MailEvent extends Event
{
	/**
	 * @var \yii\mail\MessageInterface the mail message being send.
	 */
	public $message;

	/**
	 * @var boolean if message was sent successfully.
	 */
	public $isSuccessful;

	/**
	 * @var boolean whether to continue sending an email. Event handlers of
	 * [[QueueMailer::EVENT_BEFORE_SEND]] may set this property to decide whether
	 * to continue send or not.
	 */
	public $isValid = true;
}
