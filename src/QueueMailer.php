<?php
/**
 * QueueMailer
 *
 * PHP Version 5.4.17
 *
 * @author chris <chris@chrisboehme.de>
 * @copyright 2015 chris---
 */

namespace cbm\mailqueue;

use yii;
use yii\base\Component;
use yii\mail\MailerInterface;
use yii\mail\MessageInterface;

/**
 * This class QueueMailer [...]
 *
 * @category [...]
 * @package common\components
 */
class QueueMailer extends Component implements MailerInterface
{
	/**
	 * @event MailEvent an event raised right before send.
	 * You may set [[MailEvent::isValid]] to be false to cancel the send.
	 */
	const EVENT_BEFORE_SEND = 'beforeSend';
	/**
	 * @event MailEvent an event raised right after send.
	 */
	const EVENT_AFTER_SEND = 'afterSend';

	/**
	 * @var string message default class name.
	 */
	public $messageClass = 'cbm\mailqueue\MailMessage';

	/**
	 * @var string message queue message default class name
	 */
	public $mqMessageClass;

	public $mqConfig;

	public $useFileTransport;
	public $fileTransportPath = '@runtime/mail';
	public $fileTransportCallback;

	/**
	 * @var array the configuration that should be applied to any newly created
	 * email message instance by [[createMessage()]] or [[compose()]]. Any valid property defined
	 * by [[MessageInterface]] can be configured, such as `from`, `to`, `subject`, `textBody`, `htmlBody`, etc.
	 *
	 * For example:
	 *
	 * ~~~
	 * [
	 *     'charset' => 'UTF-8',
	 *     'from' => 'noreply@mydomain.com',
	 *     'bcc' => 'developer@mydomain.com',
	 * ]
	 * ~~~
	 */
	public $messageConfig = [];

	public function init()
	{
		parent::init();

		if (!isset($this->mqMessageClass))
		{
			throw new yii\base\InvalidConfigException('"mqMessageClass" not set.');
		}
	}

	/**
	 * @inherit
	 */
	public function compose($view = null, array $params = [])
	{
		if ($view !== null && !is_array($view) && !is_string($view) && !empty($view))
		{
			throw new yii\base\InvalidParamException('Invalid argument for "view".');
		}

		/* @var MailMessage $message */
		$message = $this->createMessage();
		$message->setView($view);
		$message->setViewParams($params);


		return $message;
	}

	/**
	 * @inherit
	 */
	public function send($message)
	{
		/* @var MailMessage $message */
		if (!$this->beforeSend($message))
		{
			return false;
		}

		if ($this->fileTransportCallback !== null)
		{
			$path = Yii::getAlias($this->fileTransportPath);
			$message->setPathToSaveTo($path);
			$file = call_user_func($this->fileTransportCallback, $this, $message);
			$message->setFileToSaveTo($file);
		}

		/* @var QueueMessageInterface $queueMessage */
		$cfg = $this->mqConfig;
		$cfg['class'] = $this->mqMessageClass;
		$queueMessage = Yii::createObject($cfg);
		$queueMessage->setData((string) $message);

		Yii::trace('Transfer email "' . $message->getSubject() . '" to MQ', __METHOD__);

		$isSuccessful = $queueMessage->sendMessage();
		$this->afterSend($message, $isSuccessful);

		return $isSuccessful;
	}

	/**
	 * @inherit
	 */
	public function sendMultiple(array $messages)
	{
		$successCount = 0;
		foreach ($messages as $message)
		{
			if ($this->send($message))
			{
				$successCount++;
			}
		}

		return $successCount;
	}

	/**
	 * Creates a new message instance.
	 * The newly created instance will be initialized with the configuration specified by [[messageConfig]].
	 * If the configuration does not specify a 'class', the [[messageClass]] will be used as the class
	 * of the new message instance.
	 * @return MessageInterface MailMessage instance
	 */
	protected function createMessage()
	{
		$config = $this->messageConfig;
		if (!array_key_exists('class', $config)) {
			$config['class'] = $this->messageClass;
		}
		$config['mailer'] = $this;

		return Yii::createObject($config);
	}

	/**
	 * This method is invoked right before mail send.
	 * You may override this method to do last-minute preparation for the message.
	 * If you override this method, please make sure you call the parent implementation first.
	 * @param MessageInterface $message
	 * @return boolean whether to continue sending an email.
	 */
	public function beforeSend($message)
	{
		$event = new MailEvent(['message' => $message]);
		$this->trigger(self::EVENT_BEFORE_SEND, $event);

		return $event->isValid;
	}

	/**
	 * This method is invoked right after mail was send.
	 * You may override this method to do some postprocessing or logging based on mail send status.
	 * If you override this method, please make sure you call the parent implementation first.
	 * @param MessageInterface $message
	 * @param boolean $isSuccessful
	 */
	public function afterSend($message, $isSuccessful)
	{
		$event = new MailEvent(['message' => $message, 'isSuccessful' => $isSuccessful]);
		$this->trigger(self::EVENT_AFTER_SEND, $event);
	}
}