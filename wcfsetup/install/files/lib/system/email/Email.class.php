<?php
namespace wcf\system\email;
use wcf\system\application\ApplicationHandler;
use wcf\system\background\job\AbstractBackgroundJob;
use wcf\system\background\job\EmailDeliveryBackgroundJob;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\email\mime\AbstractMimePart;
use wcf\system\email\mime\IRecipientAwareMimePart;
use wcf\system\event\EventHandler;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Represents a RFC 5322 message using the Mime format as defined in RFC 2045.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email
 * @since	3.0
 */
class Email {
	/**
	 * From header
	 * @var	Mailbox
	 */
	protected $sender = null;
	
	/**
	 * Reply-To header
	 * @var	Mailbox
	 */
	protected $replyTo = null;
	
	/**
	 * Recipients of this email.
	 * @var	array
	 */
	protected $recipients = [];
	
	/**
	 * Message-Id header
	 * @var	string
	 */
	protected $messageID = null;
	
	/**
	 * References header
	 * @var	string[]
	 */
	protected $references = [];
	
	/**
	 * In-Reply-To header
	 * @var	string[]
	 */
	protected $inReplyTo = [];
	
	/**
	 * List-Id header
	 * @var	string
	 * @since 5.3
	 */
	protected $listId;
	
	/**
	 * Human readable part of the List-Id header
	 * @var	string
	 * @since 5.3
	 */
	protected $listIdHuman;
	
	/**
	 * List-Unsubscribe URI
	 * @var	string
	 * @since 5.3
	 */
	protected $listUnsubscribe;
	
	/**
	 * Whether the listUnsubscribe URI has One-Click support
	 * @var	boolean
	 * @since 5.3
	 */
	protected $listUnsubscribeOneClick = false;
	
	/**
	 * Date header
	 * @var	\DateTime
	 */
	protected $date = null;
	
	/**
	 * Subject header
	 * @var	string
	 */
	protected $subject = '';
	
	/**
	 * User specified X-* headers
	 * @var	array
	 */
	protected $extraHeaders = [];
	
	/**
	 * The body of this Email.
	 * @var	AbstractMimePart
	 */
	protected $body = null;
	
	/**
	 * Mail host for use in the Message-Id
	 * @var	string
	 */
	private static $host = null;
	
	/**
	 * Returns the mail host for use in the Message-Id.
	 * 
	 * @return	string
	 */
	public static function getHost() {
		if (self::$host === null) {
			self::$host = ApplicationHandler::getInstance()->getApplication('wcf')->domainName;
		}
		
		return self::$host;
	}
	
	/**
	 * Sets the email's 'Subject'.
	 * 
	 * @param	string	$subject
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	/**
	 * Returns the email's 'Subject'.
	 * 
	 * @return	string
	 */
	public function getSubject() {
		return $this->subject;
	}
	
	/**
	 * Sets the email's 'Date'.
	 * 
	 * @param	\DateTime	$date
	 */
	public function setDate(\DateTime $date = null) {
		$this->date = $date;
	}
	
	/**
	 * Returns the email's 'Date'.
	 * If no header is set yet the current time will automatically be set.
	 * 
	 * @return	\DateTime
	 */
	public function getDate() {
		if ($this->date === null) {
			$this->date = DateUtil::getDateTimeByTimestamp(TIME_NOW);
		}
		
		return $this->date;
	}
	
	/**
	 * Sets the part left of the at sign (@) in the email's 'Message-Id'.
	 * 
	 * @param	string		$messageID
	 * @throws	\DomainException
	 */
	public function setMessageID($messageID = null) {
		if ($messageID === null) {
			$this->messageID = null;
			return;
		}
		
		if (!preg_match('(^'.EmailGrammar::getGrammar('id-left').'$)', $messageID)) {
			throw new \DomainException("The given message id '".$messageID."' is invalid. Note: You must not specify the part right of the at sign (@).");
		}
		if (strlen($messageID) > 200) {
			throw new \DomainException("The given message id '".$messageID."' is not allowed. The maximum allowed length is 200 bytes.");
		}
		
		$this->messageID = $messageID;
	}
	
	/**
	 * Returns the email's full 'Message-Id' including the host.
	 * If no header is set yet a message id will automatically be generated and set.
	 * 
	 * @return	string
	 */
	public function getMessageID() {
		if ($this->messageID === null) {
			$this->messageID = bin2hex(\random_bytes(20));
		}
		
		return '<'.$this->messageID.'@'.self::getHost().'>';
	}
	
	/**
	 * Adds a message id to the email's 'In-Reply-To'.
	 * 
	 * @param	string		$messageID
	 * @throws	\DomainException
	 */
	public function addInReplyTo($messageID) {
		if (!preg_match('(^'.EmailGrammar::getGrammar('msg-id').'$)', $messageID)) {
			throw new \DomainException("The given reference '".$messageID."' is invalid.");
		}
		
		$this->inReplyTo[$messageID] = $messageID;
	}
	
	/**
	 * Removes a message id from the email's 'In-Reply-To'.
	 * 
	 * @param	string	$messageID
	 */
	public function removeInReplyTo($messageID) {
		unset($this->inReplyTo[$messageID]);
	}
	
	/**
	 * Returns the email's 'In-Reply-To' message ids.
	 * 
	 * @return	string[]
	 */
	public function getInReplyTo() {
		return $this->inReplyTo;
	}
	
	/**
	 * Adds a message id to the email's 'References'.
	 * 
	 * @param	string		$messageID
	 * @throws	\DomainException
	 */
	public function addReferences($messageID) {
		if (!preg_match('(^'.EmailGrammar::getGrammar('msg-id').'$)', $messageID)) {
			throw new \DomainException("The given reference '".$messageID."' is invalid.");
		}
		
		$this->references[$messageID] = $messageID;
	}
	
	/**
	 * Removes a message id from the email's 'References'.
	 * 
	 * @param	string	$messageID
	 */
	public function removeReferences($messageID) {
		unset($this->references[$messageID]);
	}
	
	/**
	 * Returns the email's 'References' message ids.
	 * 
	 * @return	string[]
	 */
	public function getReferences() {
		return $this->references;
	}
	
	/**
	 * Sets the list-label part of the email's 'List-Id'.
	 * 
	 * @param	string		$listId
	 * @param	string		$humanReadable
	 * @throws	\DomainException
	 * @since 5.3
	 */
	public function setListID($listId, $humanReadable = null) {
		if ($listId === null) {
			$this->listId = null;
			return;
		}
		
		if (!preg_match('(^'.EmailGrammar::getGrammar('list-label').'$)', $listId)) {
			throw new \DomainException("The given list id '".$listId."' is invalid.");
		}
		if (strlen($listId) > 200) {
			throw new \DomainException("The given list id '".$listId."' is not allowed. The maximum allowed length is 200 bytes.");
		}
		if ($humanReadable !== null) {
			$humanReadable = EmailGrammar::encodeHeader($humanReadable);
			if (!preg_match('(^'.EmailGrammar::getGrammar('phrase').'$)', $humanReadable)) {
				throw new \DomainException("The given human readable name '".$humanReadable."' is invalid.");
			}
		}
		
		$this->listId = $listId;
		$this->listIdHuman = $humanReadable;
	}
	
	/**
	 * Returns the email's full 'List-Id' including the host. Returns 'null'
	 * if no 'List-Id' is set.
	 * 
	 * @return	?string
	 * @since 5.3
	 */
	public function getListID() {
		if ($this->listId === null) {
			return null;
		}
		
		return ($this->listIdHuman ? $this->listIdHuman.' ' : '').'<'.$this->listId.'.list-id.'.self::getHost().'>';
	}
	
	/**
	 * Sets the URI for the 'List-Unsubscribe' header.
	 * 
	 * If $supportsOneClick is set to true the 'List-Unsubscribe-Post' header
	 * with the value 'List-Unsubscribe=One-Click' is added.
	 * 
	 * @param	string		$uri
	 * @param	boolean		$supportsOneClick
	 * @since 5.3
	 */
	public function setListUnsubscribe($uri, $supportsOneClick = false) {
		if ($uri === null) {
			$this->listUnsubscribe = null;
			return;
		}
		
		$this->listUnsubscribe = $uri;
		$this->listUnsubscribeOneClick = $supportsOneClick;
	}
	
	/**
	 * Returns the email's full 'List-Id' including the host. Returns 'null'
	 * if no 'List-Id' is set.
	 * 
	 * @return	?string
	 * @since 5.3
	 */
	public function getListUnsubscribeUri() {
		return $this->listUnsubscribe;
	}
	
	/**
	 * Sets the email's 'From'.
	 * 
	 * @param	Mailbox		$sender
	 */
	public function setSender(Mailbox $sender = null) {
		$this->sender = $sender;
	}

	/**
	 * Returns the email's 'From'.
	 * If no header is set yet the MAIL_FROM_ADDRESS will automatically be set.
	 * 
	 * @return	Mailbox
	 */
	public function getSender() {
		if ($this->sender === null) {
			$this->sender = new Mailbox(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
		}
		
		return $this->sender;
	}
	
	/**
	 * Sets the email's 'Reply-To'.
	 * 
	 * @param	Mailbox		$replyTo
	 */
	public function setReplyTo(Mailbox $replyTo = null) {
		$this->replyTo = $replyTo;
	}
	
	/**
	 * Returns the email's 'Reply-To'.
	 * If no header is set yet the MAIL_ADMIN_ADDRESS will automatically be set.
	 * 
	 * @return	Mailbox
	 */
	public function getReplyTo() {
		if ($this->replyTo === null) {
			$this->replyTo = new Mailbox(MAIL_ADMIN_ADDRESS);
		}
		
		return $this->replyTo;
	}
	
	/**
	 * Adds a recipient to this email.
	 * 
	 * @param	Mailbox		$recipient
	 * @param	string		$type		One of 'to', 'cc', 'bcc'
	 * @throws	\DomainException
	 */
	public function addRecipient(Mailbox $recipient, $type = 'to') {
		switch ($type) {
			case 'to':
			case 'cc':
			case 'bcc':
			break;
			default:
				throw new \DomainException("The given type '".$type."' is invalid. Must be one of 'to', 'cc', 'bcc'.");
		}
		
		if (isset($this->recipients[$recipient->getAddress()])) {
			throw new \UnexpectedValueException("There already is a recipient with the email address '".$recipient->getAddress()."'. If you want to change the \$type use removeRecipient() first.");
		}
		
		$this->recipients[$recipient->getAddress()] = [
			'type' => $type,
			'mailbox' => $recipient
		];
	}
	
	/**
	 * Removes a recipient from this email.
	 * 
	 * @param	Mailbox		$recipient
	 */
	public function removeRecipient(Mailbox $recipient) {
		unset($this->recipients[$recipient->getAddress()]);
	}
	
	/**
	 * Returns the email's recipients as an array of [ $recipient, $type ] tuples.
	 * 
	 * @return	array
	 */
	public function getRecipients() {
		return $this->recipients;
	}
	
	/**
	 * Adds a custom X-* header to the email.
	 * 
	 * @param	string		$header
	 * @param	string		$value
	 * @throws	\DomainException
	 */
	public function addHeader($header, $value) {
		$header = mb_strtolower($header);
		if (!StringUtil::startsWith($header, 'x-')) {
			throw new \DomainException("The header '".$header."' may not be set. You may only set user defined headers (starting with 'X-').");
		}
		
		$this->extraHeaders[] = [$header, EmailGrammar::encodeQuotedPrintableHeader($value)];
	}
	
	/**
	 * Returns an array of [ name, value ] tuples representing the email's headers.
	 * Note: You must have set a Subject and at least one recipient, otherwise fetching the
	 *       headers will fail.
	 * 
	 * @return	array
	 * @throws	\LogicException
	 */
	public function getHeaders() {
		$headers = [];
		$to = [];
		$cc = [];
		foreach ($this->getRecipients() as $recipient) {
			if ($recipient['type'] == 'to') $to[] = $recipient['mailbox'];
			else if ($recipient['type'] == 'cc') $cc[] = $recipient['mailbox'];
		}
		$headers[] = ['from', (string) $this->getSender()];
		if ($this->getReplyTo()->getAddress() !== $this->getSender()->getAddress()) {
			$headers[] = ['reply-to', (string) $this->getReplyTo()];
		}
		
		if ($to) {
			$headers[] = ['to', implode(",\r\n   ", $to)];
		}
		else {
			throw new \LogicException("Cannot generate message headers, you must specify a recipient.");
		}
		
		if ($cc) {
			$headers[] = ['cc', implode(",\r\n   ", $cc)];
		}
		if ($this->getSubject()) {
			$headers[] = ['subject', EmailGrammar::encodeQuotedPrintableHeader($this->getSubject())];
		}
		else {
			throw new \LogicException("Cannot generate message headers, you must specify a subject.");
		}
		
		$headers[] = ['date', $this->getDate()->format(\DateTime::RFC2822)];
		$headers[] = ['message-id', $this->getMessageID()];
		if ($this->getReferences()) {
			$headers[] = ['references', implode("\r\n   ", $this->getReferences())];
		}
		if ($this->getInReplyTo()) {
			$headers[] = ['in-reply-to', implode("\r\n   ", $this->getInReplyTo())];
		}
		if ($this->getListID()) {
			$headers[] = ['list-id', $this->getListID()];
		}
		if ($this->getListUnsubscribeUri()) {
			$headers[] = ['list-unsubscribe', '<'.$this->getListUnsubscribeUri().'>'];
			if ($this->listUnsubscribeOneClick) {
				$headers[] = ['list-unsubscribe-post', 'List-Unsubscribe=One-Click'];
			}
		}
		$headers[] = ['mime-version', '1.0'];
		
		if (!$this->body) {
			throw new \LogicException("Cannot generate message headers, you must set a body.");
		}
		$headers[] = ['content-type', $this->body->getContentType()];
		if ($this->body->getContentTransferEncoding()) {
			$headers[] = ['content-transfer-encoding', $this->body->getContentTransferEncoding()];
		}
		$headers = array_merge($headers, $this->body->getAdditionalHeaders());
		
		return array_merge($headers, $this->extraHeaders);
	}
	
	/**
	 * Returns the email's headers as a string.
	 * Note: This method attempts to convert the header name to the "canonical"
	 *       case of the header (e.g. upper case at the start and after the hyphen).
	 * 
	 * @see	\wcf\system\email\Email::getHeaders()
	 * 
	 * @return	string
	 */
	public function getHeaderString() {
		return implode("\r\n", array_map(function ($item) {
			list($name, $value) = $item;
			
			switch ($name) {
				case 'message-id':
					$name = 'Message-ID';
					break;
				case 'list-id':
					$name = 'List-ID';
					break;
				case 'list-unsubscribe-post':
					// This case is identical to the default case below.
					// It is special cased, because the grammar of this header is defined
					// to be pretty tight.
					$name = 'List-Unsubscribe-Post';
					break;
				case 'mime-version':
					$name = 'MIME-Version';
					break;
				default:
					$name = preg_replace_callback('/(?:^|-)[a-z]/', function ($matches) {
						return mb_strtoupper($matches[0]);
					}, $name);
			}
			
			return $name.': '.$value;
		}, $this->getHeaders()));
	}
	
	/**
	 * Sets the body of this email.
	 * 
	 * @param	AbstractMimePart	$body
	 */
	public function setBody(AbstractMimePart $body) {
		$this->body = $body;
	}
	
	/**
	 * Returns the body of this email.
	 * 
	 * @return	AbstractMimePart
	 */
	public function getBody() {
		return $this->body;
	}
	
	/**
	 * Returns the email's body as a string.
	 * 
	 * @return	string
	 */
	public function getBodyString() {
		if ($this->body === null) {
			throw new \LogicException('Cannot generate message body, you must specify a body');
		}
		
		switch ($this->body->getContentTransferEncoding()) {
			case 'quoted-printable':
				return quoted_printable_encode(str_replace("\n", "\r\n", StringUtil::unifyNewlines($this->body->getContent())));
			break;
			case 'base64':
				return chunk_split(base64_encode($this->body->getContent()));
			break;
			case '':
				return $this->body->getContent();
		}
		
		throw new \LogicException('Unreachable');
	}
	
	/**
	 * Returns needed AbstractBackgroundJobs to deliver this email to every recipient.
	 * 
	 * @return	AbstractBackgroundJob[]
	 */
	public function getJobs() {
		$jobs = [];
		
		// ensure every header is filled in
		$this->getHeaders();
		
		// ensure the body is filled in
		if ($this->body === null) {
			throw new \LogicException('Cannot generate message body, you must specify a body');
		}
		
		foreach ($this->recipients as $recipient) {
			$mail = clone $this;
			
			if ($recipient['mailbox'] instanceof UserMailbox) {
				$mail->addHeader('X-WoltLab-Suite-Recipient', $recipient['mailbox']->getUser()->username);
			}
			
			if ($this->body instanceof IRecipientAwareMimePart) $this->body->setRecipient($recipient['mailbox']);
			
			$data = [
				'mail' => $mail,
				'recipient' => $recipient,
				'sender' => $mail->getSender(),
				'skip' => false
			];
			EventHandler::getInstance()->fireAction($this, 'getJobs', $data);
			
			// an event decided that this email should be skipped
			if ($data['skip']) continue;
			
			$jobs[] = new EmailDeliveryBackgroundJob($mail, $data['sender'], $data['recipient']['mailbox']);
		}
		
		return $jobs;
	}
	
	/**
	 * Queues this email for delivery.
	 * This is equivalent to manually queuing the jobs returned by getJobs().
	 * 
	 * @see	\wcf\system\email\Email::getJobs()
	 * @see	\wcf\system\background\BackgroundQueueHandler::enqueueIn()
	 */
	public function send() {
		$jobs = $this->getJobs();
		
		// force synchronous execution, see https://github.com/WoltLab/WCF/issues/2501
		if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
			foreach ($jobs as $job) {
				BackgroundQueueHandler::getInstance()->performJob($job, true);
			}
		}
		else {
			BackgroundQueueHandler::getInstance()->enqueueIn($jobs);
			BackgroundQueueHandler::getInstance()->forceCheck();
		}
	}
	
	/**
	 * @see	Email::getEmail()
	 */
	public function __toString() {
		return $this->getEmail();
	}
	
	/**
	 * Returns the email RFC 2822 representation of this email.
	 * 
	 * @return	string
	 */
	public function getEmail() {
		return $this->getHeaderString()."\r\n\r\n".$this->getBodyString();
	}

	/**
	 * Dumps this email to STDOUT and stops the script.
	 * 
	 * @return	string
	 */
	public function debugDump() {
		if (ob_get_level()) {
			// discard any output generated before the email was dumped, prevents email
			// being hidden inside HTML elements and therefore not visible in browser output
			ob_end_clean();
			
			// `identity` is the default "encoding" and basically means that the client
			// must treat the content as if the header did not appear in first place, this
			// also overrules the gzip header if present
			@header('Content-Encoding: identity');
			HeaderUtil::exceptionDisableGzip();
		}
		
		$dumpBody = function ($body, $depth) use (&$dumpBody) {
			$result = '';
			// @codingStandardsIgnoreStart
			if ($body instanceof mime\MimePartFacade) {
				return $dumpBody($body->getMimePart(), $depth);
			}
			if ($body instanceof mime\AbstractMultipartMimePart) {
				$result .= "<fieldset><legend><h".$depth.">".get_class($body)."</h".$depth."></legend>";
				foreach ($body->getMimeparts() as $part) {
					$result .= $dumpBody($part, $depth + 1);
				}
				$result .= '</fieldset>';
			}
			else if ($body instanceof mime\RecipientAwareTextMimePart) {
				$result .= "<fieldset><legend><h".$depth.">".get_class($body)."</h".$depth."></legend>";
				if ($body instanceof mime\HtmlTextMimePart) {
					$result .= '<iframe src="data:text/html;base64,'.base64_encode($body->getContent()).'" style="width: 100%; height: 500px; border: 0"></iframe>';
				}
				else {
					$result .= "<pre>".StringUtil::encodeHTML($body->getContent())."</pre>";
				}
				$result .= '</fieldset>';
			}
			else if ($body instanceof mime\AttachmentMimePart) {
				$result .= "<fieldset><legend><h".$depth.">".get_class($body)."</h".$depth."></legend>";
				$result .= "<dl>".implode('', array_map(function ($item) {
					return "<dt>".$item[0]."</dt><dd>".$item[1]."</dd>";
				}, $body->getAdditionalHeaders()))."</dl>";
				$result .= "<".strlen($body->getContent())." Bytes>";
				$result .= '</fieldset>';
			}
			else {
				throw new \LogicException('Bug');
			}
			// @codingStandardsIgnoreEnd

			return $result;
		};
		echo "<h1>Message Headers</h1>
<pre>".StringUtil::encodeHTML($this->getHeaderString())."</pre>
<h1>Message Body</h1>".$dumpBody($this->body, 2);

		exit;
	}
}
