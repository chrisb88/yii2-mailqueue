<?php
/**
 * MailMessage
 *
 * PHP Version 5.4.17
 *
 * @author chris <chris@chrisboehme.de>
 * @copyright 2015 chris---
 */

namespace cbm\mailqueue;

use yii\base\NotSupportedException;
use yii\helpers\Json;
use yii\mail\BaseMessage;


/**
 * This class MailMessage [...]
 *
 * @category [...]
 * @package cbm\mailqueue
 */
class MailMessage extends BaseMessage
{
	protected $params = [];

	/**
	 * @inheritdoc
	 */
	public function getCharset()
	{
		return $this->getParameter('charset');
	}

	/**
	 * @inheritdoc
	 */
	public function setCharset($charset)
	{
		$this->setParameter('charset', $charset);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getFrom()
	{
		return $this->getParameter('from');
	}

	/**
	 * @inheritdoc
	 */
	public function setFrom($from)
	{
		$this->setParameter('from', $from);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getReplyTo()
	{
		return $this->getParameter('reply-to');
	}

	/**
	 * @inheritdoc
	 */
	public function setReplyTo($replyTo)
	{
		$this->setParameter('reply-to', $replyTo);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getTo()
	{
		return $this->getParameter('to');
	}

	/**
	 * @inheritdoc
	 */
	public function setTo($to)
	{
		$this->setParameter('to', $to);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getCc()
	{
		return $this->getParameter('cc');
	}

	/**
	 * @inheritdoc
	 */
	public function setCc($cc)
	{
		$this->setParameter('cc', $cc);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getBcc()
	{
		return $this->getParameter('bcc');
	}

	/**
	 * @inheritdoc
	 */
	public function setBcc($bcc)
	{
		$this->setParameter('bcc', $bcc);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getSubject()
	{
		return $this->getParameter('subject');
	}

	/**
	 * @inheritdoc
	 */
	public function setSubject($subject)
	{
		$this->setParameter('subject', $subject);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function setTextBody($text)
	{
		$this->setBody($text, 'text');

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function setHtmlBody($html)
	{
		$this->setBody($html, 'html');

		return $this;
	}

	public function setPathToSaveTo($path)
	{
		$this->setParameter('pathToSave', $path);

		return $this;
	}

	public function setFileToSaveTo($file)
	{
		$this->setParameter('fileToSave', $file);

		return $this;
	}

	/**
	 * Sets the message body.
	 * If body is already set and its content type matches given one, it will
	 * be overridden, if content type miss match the multipart message will be composed.
	 * @param string $body body content.
	 * @param string $contentType body content type.
	 * @return self
	 */
	protected function setBody($body, $contentType)
	{
		$this->setParameter('body_' . $contentType, $body);

		return $this;
	}

	public function setView($view)
	{
		$this->setParameter('view', $view);
	}

	public function setViewParams($params)
	{
		$this->setParameter('viewParams', $params);
	}

	/**
	 * @inheritdoc
	 */
	public function attach($fileName, array $options = [])
	{
		throw new NotSupportedException('"attach" is not supported.');
	}

	/**
	 * @inheritdoc
	 */
	public function attachContent($content, array $options = [])
	{
		throw new NotSupportedException('"attachContent" is not supported.');
	}

	/**
	 * @inheritdoc
	 */
	public function embed($fileName, array $options = [])
	{
		throw new NotSupportedException('"embed" is not supported.');
	}

	/**
	 * @inheritdoc
	 */
	public function embedContent($content, array $options = [])
	{
		throw new NotSupportedException('"embedContent" is not supported.');
	}

	/**
	 * @inheritdoc
	 */
	public function toString()
	{
		return $this->getJson();
	}

	public function getJson()
	{
		return Json::encode($this->params);
	}

	private function getParameter($key)
	{
		if (isset($this->params[$key]))
		{
			return $this->params[$key];
		}

		return null;
	}

	private function setParameter($key, $value)
	{
		$this->params[$key] = $value;
	}
}