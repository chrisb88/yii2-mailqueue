<?php
/**
 * EmailTaskController
 *
 * PHP Version 5.4.17
 *
 * @author chris <chris@chrisboehme.de>
 * @copyright 2015 chris---
 */

namespace console\controllers;

use yii;
use yii\console\Controller;
use PhpAmqpLib\Connection\AMQPConnection;
use \PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * This class EmailTaskController [...]
 *
 * @category [...]
 * @package console\controllers
 */
class EmailTaskController extends Controller
{
	// connection defaults
	public $host = 'localhost';
	public $port = 5672;
	public $user = 'guest';
	public $password = 'guest';
	public $vhost = '/';
	public $insist = false;
	public $login_method = 'AMQPLAIN';
	public $login_response;
	public $locale = 'en_US';
	public $connection_timeout = 3;
	public $read_write_timeout = 3;
	public $context;

	// queue defaults
	public $queue = '';

	/**
	 * @var AMQPConnection
	 */
	protected $connection;

	/**
	 * @var AMQPChannel
	 */
	protected $channel;

	public function init()
	{
		Yii::configure($this, Yii::$app->params['emailTaskConsumer']);
	}

	public function actionIndex()
	{
		Yii::info('Started email task', __METHOD__);

		$this->setupConnection();

		echo '[*] Waiting for messages. To exit press CTRL+C', "\n";

		$this->channel->basic_qos(null, 1, null);
		$this->channel->basic_consume($this->queue, '', false, false, false, false, [$this, 'processEmail']);

		while(count($this->channel->callbacks)) {
			$this->channel->wait();
		}

		echo 'Shutting down...', "\n";

		Yii::info('Shutting down...', __METHOD__);
		Yii::trace('Disconnecting...', __METHOD__);
		$this->channel->close();
		$this->connection->close();

		return self::EXIT_CODE_NORMAL;
	}

	protected function setupConnection()
	{
		Yii::trace('Connecting to broker...', __METHOD__);
		$this->connection = new AMQPConnection($this->host, $this->port, $this->user, $this->password, $this->vhost, $this->insist,
			$this->login_method, $this->login_response, $this->locale, $this->connection_timeout, $this->read_write_timeout, $this->context
		);

		$this->channel = $this->connection->channel();
		$this->channel->queue_declare($this->queue, false, true, false, false);
	}

	public function processEmail(AMQPMessage $msg)
	{
		echo ' [x] - ' . date('Y-m-d H:i:s') . " - Message received\n";
		Yii::info('Received message', __METHOD__);
		Yii::trace('Message: ' . $msg->body, __METHOD__);

		$this->composeAndSendEmail($msg);

		echo ' [x] Done' . "\n";

		/* @var AMQPChannel $channel */
		$channel = $msg->delivery_info['channel'];

		// tell the broker that we have processed the message
		$channel->basic_ack($msg->delivery_info['delivery_tag']);
	}

	protected function composeAndSendEmail(AMQPMessage $msg)
	{
		try {
			$message = yii\helpers\Json::decode($msg->body, false);
		} catch (yii\base\InvalidParamException $e) {
			Yii::error('Could not decode message: ' . $msg->body, __METHOD__);
			return;
		}

		echo ' [x] Sending email to: ' . $message->to . "\n";
		Yii::trace('Composing email for: ' . $message->to, __METHOD__);

		if (isset($message->fileToSave) && isset($message->pathToSave)) {
			echo '   save it to file instead: ' . $message->pathToSave . '/' . $message->fileToSave . "\n";
			$file = $message->fileToSave;
			Yii::$app->emailMailer->fileTransportPath = $message->pathToSave;
			Yii::$app->emailMailer->fileTransportCallback = function ($mailer, $message) use ($file) {
				return $file;
			};
		}

		$view = (array) $message->view;
		$params = (array) $message->viewParams;
		if (empty($view)) {
			$view = null;
		}

		try {
			/* @var yii\mail\MessageInterface $email */
			$email = Yii::$app->emailMailer->compose($view, $params);
			$email->setFrom($message->from);
			$email->setTo($message->to);
			$email->setSubject($message->subject);

			if (isset($message->body_text))
			{
				$email->setTextBody($message->body_text);
			}

			if (isset($message->body_html))
			{
				$email->setHtmlBody($message->body_html);
			}

			$emailSent = $email->send();
		} catch (\Exception $e) {
			$emailSent = false;
		}

		if (!$emailSent) {
			Yii::error('Could not send email.', __METHOD__);
		}
	}
}