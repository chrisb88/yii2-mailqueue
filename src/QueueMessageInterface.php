<?php
/**
 * QueueMessageInterface
 *
 * PHP Version 5.4.17
 *
 * @author chris <chris@chrisboehme.de>
 * @copyright 2015 chris---
 */

namespace cbm\mailqueue;

/**
 * QueueMessageInterface is the interface that should be implemented by queue mail message classes.
 * Implement here the connection and handling to your message broker.
 */
interface QueueMessageInterface
{
	/**
	 * Called after the class was created. You can setup your broker connection here.
	 */
	public function init();

	/**
	 * Sets the data body of this message.
	 * @param mixed $data MailMessage body
	 * @return static self reference
	 */
	public function setData($data);

	/**
	 * Send the message to the broker.
	 * Should return true or throw exceptions.
	 * @return true
	 */
	public function sendMessage();
}
