<?php
/**
 * RabbitMessage
 *
 * PHP Version 5.4.17
 *
 * @author chris <chris@chrisboehme.de>
 * @copyright 2015 chris---
 */

namespace cbm\mailqueue;

use yii\base\Component;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * This class RabbitMessage sends a message to the RabbitMQ broker.
 *
 * @category [...]
 * @package cbm\mailqueue
 */
class RabbitMessage extends Component implements QueueMessageInterface
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
	public $queuePassive = false;
	public $queueDurable = true;
	public $queueExclusive = false;
	public $queueAutoDelete = false;
	public $queueNowait = false;
	public $queueArguments;
	public $queueTicket;

	// publish defaults
	public $publishExchange = '';
	public $publishRoutingKey = '';
	public $publishMandatory = false;
	public $publishImmediate = false;
	public $publishTicket;

	// message defaults
	protected $messageProperties = [
		'delivery_method' => 2, // persistent messages
	];

	/**
	 * @var AMQPConnection
	 */
	protected $connection;

	/**
	 * @var \PhpAmqpLib\Channel\AMQPChannel
	 */
	protected $channel;

	/**
	 * @var mixed
	 */
	protected $data;

	/**
	 * @inherit
	 */
	public function init()
	{
		$this->connection = new AMQPConnection($this->host, $this->port, $this->user, $this->password, $this->vhost, $this->insist,
			$this->login_method, $this->login_response, $this->locale, $this->connection_timeout, $this->read_write_timeout, $this->context
		);

		$this->channel = $this->connection->channel();
		$this->channel->queue_declare($this->queue, $this->queuePassive, $this->queueDurable, $this->queueExclusive, $this->queueAutoDelete);
	}

	/**
	 * @inherit
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * @inherit
	 */
	public function sendMessage()
	{
		$msg = new AMQPMessage($this->data, $this->messageProperties);
		$this->channel->basic_publish($msg, $this->publishExchange, $this->publishRoutingKey, $this->publishMandatory, $this->publishImmediate, $this->publishTicket);

		$this->channel->close();
		$this->connection->close();

		return true;
	}
}
