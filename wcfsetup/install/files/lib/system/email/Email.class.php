<?php
namespace wcf\system\email;
use wcf\system\background\job\AbstractBackgroundJob;
use wcf\system\background\job\EmailDeliveryBackgroundJob;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\email\mime\AbstractMimePart;
use wcf\system\email\mime\IRecipientAwareMimePart;
use wcf\system\email\mime\TextMimePart;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\util\DateUtil;
use wcf\util\StringUtil;

/**
 * Represents a RFC 5322 message using the Mime format as defined in RFC 2045.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email
 * @category	Community Framework
 * @since	2.2
 */
class Email {
	/**
	 * From header
	 * @var	\wcf\system\email\Mailbox
	 */
	protected $sender = null;
	
	/**
	 * Reply-To header
	 * @var	\wcf\system\email\Mailbox
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
	 * @var	Mailbox[]
	 */
	protected $references = [];
	
	/**
	 * In-Reply-To header
	 * @var	Mailbox[]
	 */
	protected $inReplyTo = [];
	
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
	 * Text parts of this email
	 * @var	array
	 */
	protected $text = [];
	
	/**
	 * Attachments of this email
	 * @var	array
	 */
	protected $attachments = [];
	
	/**
	 * Boundary between the 'Text' parts of this email
	 * @var	string
	 */
	private $textBoundary;
	
	/**
	 * Boundary between the mime parts of this email
	 * @var	string
	 */
	private $mimeBoundary;
	
	/**
	 * Mail host for use in the Message-Id
	 * @var	string
	 */
	private static $host = null;
	
	/**
	 * Generates boundaries for the mime parts.
	 */
	public function __construct() {
		$this->textBoundary = "WoltLab_Community_Framework=_".StringUtil::getRandomID();
		$this->mimeBoundary = "WoltLab_Community_Framework=_".StringUtil::getRandomID();
	}
	
	/**
	 * Returns the mail host for use in the Message-Id.
	 * 
	 * @return	string
	 */
	public static function getHost() {
		if (self::$host === null) {
			self::$host = (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : '';
			if (empty(self::$host)) {
				self::$host = gethostname();
				if (self::$host === false) {
					self::$host = 'localhost';
				}
			}
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
	 * @throws	SystemException
	 */
	public function setMessageID($messageID = null) {
		if ($messageID === null) {
			$this->messageID = null;
			return;
		}
		
		if (!preg_match('(^'.EmailGrammar::getGrammar('id-left').'$)', $messageID)) {
			throw new SystemException("The given message id '".$messageID."' is invalid. Note: You must not specify the part right of the at sign (@).");
		}
		if (strlen($messageID) > 50) {
			throw new SystemException("The given message id '".$messageID."' is not allowed. The maximum allowed length is 50 bytes.");
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
			$this->messageID = StringUtil::getRandomID();
		}
		
		return '<'.$this->messageID.'@'.self::getHost().'>';
	}
	
	/**
	 * Adds a message id to the email's 'In-Reply-To'.
	 * 
	 * @param	string		$messageID
	 * @throws	SystemException
	 */
	public function addInReplyTo($messageID) {
		if (!preg_match('(^'.EmailGrammar::getGrammar('msg-id').'$)', $messageID)) {
			throw new SystemException("The given reference '".$messageID."' is invalid.");
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
	 * @throws	SystemException
	 */
	public function addReferences($messageID) {
		if (!preg_match('(^'.EmailGrammar::getGrammar('msg-id').'$)', $messageID)) {
			throw new SystemException("The given reference '".$messageID."' is invalid.");
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
	 * Sets the email's 'From'.
	 * 
	 * @param	\wcf\system\email\Mailbox	$sender
	 */
	public function setSender(Mailbox $sender = null) {
		$this->sender = $sender;
	}

	/**
	 * Returns the email's 'From'.
	 * If no header is set yet the MAIL_FROM_ADDRESS will automatically be set.
	 * 
	 * @return	\wcf\system\email\Mailbox
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
	 * @return	\wcf\system\email\Mailbox
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
	 * @throws	SystemException
	 */
	public function addRecipient(Mailbox $recipient, $type = 'to') {
		switch ($type) {
			case 'to':
			case 'cc':
			case 'bcc':
			break;
			default:
				throw new SystemException("The given type '".$type."' is invalid. Must be one of 'to', 'cc', 'bcc'.");
		}
		
		$this->recipients[$recipient->getAddress()] = [$type, $recipient];
	}
	
	/**
	 * Removes a recipient from this email.
	 * 
	 * @param	\wcf\system\email\Mailbox	$recipient
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
	 * @throws	SystemException
	 */
	public function addHeader($header, $value) {
		$header = mb_strtolower($header);
		if (!StringUtil::startsWith($header, 'x-')) {
			throw new SystemException("The header '".$header."' may not be set. You may only set user defined headers (starting with 'X-').");
		}
		
		$this->extraHeaders[] = [$header, EmailGrammar::encodeQuotedPrintableHeader($value)];
	}
	
	/**
	 * Adds a mime part to this email. Should be either \wcf\system\email\mime\TextMimePart
	 * or \wcf\system\email\mime\AttachmentMimePart.
	 * The given priority determines the ordering within the Email. A higher priority
	 * mime part will be further down the email (see RFC 2046, 5.1.4).
	 * 
	 * @param	AbstractMimePart	$part
	 * @param	integer			$priority
	 * @throws	SystemException
	 */
	public function addMimePart(AbstractMimePart $part, $priority = 1000) {
		foreach ($part->getAdditionalHeaders() as $header) {
			$header[0] = mb_strtolower($header[0]);
			if ($header[0] == 'content-type' || $header[0] == 'content-transfer-encoding') {
				throw new SystemException("The header '".$header[0]."' may not be set. Use the proper methods.");
			}
			
			if (!StringUtil::startsWith($header[0], 'x-') && !StringUtil::startsWith($header[0], 'content-')) {
				throw new SystemException("The header '".$header[0]."' may not be set. You may only set headers starting with 'X-' or 'Content-'.");
			}
		}
		
		switch ($part->getContentTransferEncoding()) {
			case 'base64':
			case 'quoted-printable':
			break;
			default:
				throw new SystemException("The Content-Transfer-Encoding '".$part->getContentTransferEncoding()."' may not be set. You may only use 'quoted-printable' or 'base64'.");
		}
		
		if ($part instanceof TextMimePart) {
			$this->text[] = [$priority, $part];
		}
		else {
			$this->attachments[] = [$priority, $part];
		}
	}
	
	/**
	 * Returns the text mime parts of this email.
	 * 
	 * @return	array
	 */
	public function getText() {
		return $this->text;
	}
	
	/**
	 * Returns the attachments (i.e. the mime parts that are not a TextMimePart) of this email.
	 * 
	 * @return	array
	 */
	public function getAttachments() {
		return $this->attachments;
	}
	
	/**
	 * Returns an array of [ name, value ] tuples representing the email's headers.
	 * Note: You must have set a Subject and at least one recipient, otherwise fetching the
	 *       headers will fail.
	 * 
	 * @return	array
	 * @throws	SystemException
	 */
	public function getHeaders() {
		$headers = [];
		$to = [];
		$cc = [];
		foreach ($this->getRecipients() as $recipient) {
			if ($recipient[0] == 'to') $to[] = $recipient[1];
			else if ($recipient[0] == 'cc') $cc[] = $recipient[1];
		}
		$headers[] = ['from', (string) $this->getSender()];
		if ($this->getReplyTo()->getAddress() !== $this->getSender()->getAddress()) {
			$headers[] = ['reply-to', (string) $this->getReplyTo()];
		}
		
		if ($to) {
			$headers[] = ['to', implode(",\r\n   ", $to)];
		}
		else {
			throw new SystemException("Cannot generate message headers, you must specify a recipient.");
		}
		
		if ($cc) {
			$headers[] = ['cc', implode(",\r\n   ", $cc)];
		}
		if ($this->getSubject()) {
			$headers[] = ['subject', EmailGrammar::encodeQuotedPrintableHeader($this->getSubject())];
		}
		else {
			throw new SystemException("Cannot generate message headers, you must specify a subject.");
		}
		
		$headers[] = ['date', $this->getDate()->format(\DateTime::RFC2822)];
		$headers[] = ['message-id', $this->getMessageID()];
		if ($this->getReferences()) {
			$headers[] = ['references', implode(' ', $this->getReferences())];
		}
		if ($this->getInReplyTo()) {
			$headers[] = ['in-reply-to', implode(' ', $this->getInReplyTo())];
		}
		$headers[] = ['mime-version', '1.0'];
		
		if (!$this->text) {
			throw new SystemException("Cannot generate message headers, you must specify at least one 'Text' part.");
		}
		if ($this->attachments) {
			$headers[] = ['content-type', "multipart/mixed;\r\n   boundary=\"".$this->mimeBoundary."\""];
		}
		else {
			if (count($this->text) > 1) {
				$headers[] = ['content-type', "multipart/alternative;\r\n   boundary=\"".$this->textBoundary."\""];
			}
			else {
				$headers[] = ['content-type', $this->text[0][1]->getContentType()];
				$headers[] = ['content-transfer-encoding', $this->text[0][1]->getContentTransferEncoding()];
				$headers = array_merge($headers, $this->text[0][1]->getAdditionalHeaders());
			}
		}
		
		return array_merge($headers, $this->extraHeaders);
	}
	
	/**
	 * Returns the email's headers as a string.
	 * @see	\wcf\system\email\Email::getHeaders()
	 * 
	 * @return	string
	 */
	public function getHeaderString() {
		return implode("\r\n", array_map(function ($item) {
			return implode(': ', $item);
		}, $this->getHeaders()));
	}
	
	/**
	 * Returns the email's body as a string.
	 * 
	 * @return	string
	 */
	public function getBodyString() {
		$text = "";
		$body = "";
		
		if (count($this->text) > 1 || $this->attachments) {
			$body .= StringUtil::wordwrap("This is a MIME encoded email. As you are seeing this your user agent does not support these.");
			$body .= "\r\n\r\n";
		}
		
		usort($this->text, function ($a, $b) {
			return $a[0] - $b[0];
		});
		foreach ($this->text as $part) {
			if (count($this->text) > 1) {
				$text .= "--".$this->textBoundary."\r\n";
			}
			if (count($this->text) > 1 || $this->attachments) {
				$text .= "content-type: ".$part[1]->getContentType()."\r\n";
				$text .= "content-transfer-encoding: ".$part[1]->getContentTransferEncoding()."\r\n";
				if ($part[1]->getAdditionalHeaders()) {
					$text .= implode("\r\n", array_map(function ($item) {
						return implode(': ', $item);
					}, $part[1]->getAdditionalHeaders()))."\r\n";
				}
				$text .= "\r\n";
			}
			switch ($part[1]->getContentTransferEncoding()) {
				case 'quoted-printable':
					$text .= quoted_printable_encode($part[1]->getContent());
				break;
				case 'base64':
					$text .= chunk_split(base64_encode($part[1]->getContent()));
				break;
			}
			$text .= "\r\n";
		}
		if (count($this->text) > 1) {
			$text .= "--".$this->textBoundary."--\r\n";
		}
		
		if ($this->attachments) {
			$body .= "--".$this->mimeBoundary."\r\n";
			if (count($this->text) > 1) {
				$body .= "Content-Type: multipart/alternative;\r\n   boundary=\"".$this->textBoundary."\"\r\n";
				$body .= "\r\n";
			}
			$body .= $text;
			
			foreach ($this->attachments as $part) {
				$body .= "\r\n--".$this->mimeBoundary."\r\n";
				$body .= "content-type: ".$part[1]->getContentType()."\r\n";
				$body .= "content-transfer-encoding: ".$part[1]->getContentTransferEncoding()."\r\n";
				if ($part[1]->getAdditionalHeaders()) {
					$body .= implode("\r\n", array_map(function ($item) {
						return implode(': ', $item);
					}, $part[1]->getAdditionalHeaders()))."\r\n";
				}
				$body .= "\r\n";
				switch ($part[1]->getContentTransferEncoding()) {
					case 'quoted-printable':
						$body .= quoted_printable_encode($part[1]->getContent());
					break;
					case 'base64':
						$body .= chunk_split(base64_encode($part[1]->getContent()));
					break;
				}
				$body .= "\r\n";
			}
			$body .= "--".$this->mimeBoundary."--\r\n";
		}
		else {
			$body .= $text;
		}
		
		return $body;
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
		
		foreach ($this->recipients as $recipient) {
			$mail = clone $this;
			
			if ($recipient[1] instanceof UserMailbox) {
				$mail->addHeader('X-Community-Framework-Recipient', $recipient[1]->getUser()->username);
			}
			
			foreach (array_merge($mail->getText(), $mail->getAttachments()) as $mimePart) {
				if ($mimePart[1] instanceof IRecipientAwareMimePart) $mimePart[1]->setRecipient($recipient[1]);
			}
			
			$data = ['mail' => $mail, 'recipient' => $recipient, 'skip' => false];
			EventHandler::getInstance()->fireAction($this, 'getJobs', $data);
			
			// an event decided that this email should be skipped
			if ($data['skip']) continue;
			
			$jobs[] = new EmailDeliveryBackgroundJob($mail, $recipient[1]);
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
		BackgroundQueueHandler::getInstance()->enqueueIn($jobs);
		BackgroundQueueHandler::getInstance()->forceCheck();
	}
	
	/**
	 * Returns the email RFC 2822 representation of this email.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->getHeaderString()."\r\n\r\n".$this->getBodyString();
	}
}
