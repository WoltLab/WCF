<?php
namespace wcf\system\email;
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
	protected $recipients = [ ];
	
	/**
	 * Message-Id header
	 * @var	string
	 */
	protected $messageID = null;
	
	/**
	 * References header
	 * @var	array<\wcf\system\email\Mailbox>
	 */
	protected $references = [ ];
	
	/**
	 * In-Reply-To header
	 * @var	array<\wcf\system\email\Mailbox>
	 */
	protected $inReplyTo = [ ];
	
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
	protected $extraHeaders = [ ];
	
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
	 * @param	string	$messageID
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
			throw new SystemException("The given message id '".$message."' is not allowed. The maximum allowed length is 50 bytes.");
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
	 * @param	string	$messageID
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
	 * @return	array<string>
	 */
	public function getInReplyTo() {
		return $this->inReplyTo;
	}
	
	/**
	 * Adds a message id to the email's 'References'.
	 * 
	 * @param	string	$messageID
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
	 * @return	array<string>
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
	 * @param	\wcf\system\email\Mailbox	$sender
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
	 * @param	\wcf\system\email\Mailbox	$recipient
	 * @param	string				$type		One of 'to', 'cc', 'bcc'
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
		
		$this->recipients[$recipient->getAddress()] = [ $type, $recipient ];
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
	 * @param	string	$header
	 * @param	string	$value
	 */
	public function addHeader($header, $value) {
		$header = mb_strtolower($header);
		if (!StringUtil::startsWith($header, 'x-')) {
			throw new SystemException("The header '".$header."' may not be set. You may only set user defined headers (starting with 'X-').");
		}
		
		$this->extraHeaders[] = [ $header, EmailGrammar::encodeMimeHeader($value) ];
	}
	
	/**
	 * Returns an array of [ name, value ] tuples representing the email's headers.
	 * Note: You must have set a Subject and at least one recipient, otherwise fetching the
	 *       headers will fail.
	 * 
	 * @return	array
	 */
	public function getHeaders() {
		$headers = [ ];
		$to = [ ];
		$cc = [ ];
		foreach ($this->getRecipients() as $recipient) {
			if ($recipient[0] == 'to') $to[] = $recipient[1];
			else if ($recipient[0] == 'cc') $cc[] = $recipient[1];
		}
		$headers[] = [ 'from', (string) $this->getSender() ];
		if ($this->getReplyTo()->getAddress() !== $this->getSender()->getAddress()) {
			$headers[] = [ 'reply-to', (string) $this->getReplyTo() ];
		}
		
		if ($to) {
			$headers[] = [ 'to', implode(",\r\n   ", $to) ];
		}
		else {
			throw new SystemException("Cannot generate message headers, you must specify a recipient.");
		}
		
		if ($cc) {
			$headers[] = [ 'cc', implode(",\r\n   ", $cc) ];
		}
		if ($this->getSubject()) {
			$headers[] = [ 'subject', EmailGrammar::encodeMimeHeader($this->getSubject()) ];
		}
		else {
			throw new SystemException("Cannot generate message headers, you must specify a subject.");
		}
		
		$headers[] = [ 'date', $this->getDate()->format(\DateTime::RFC2822) ];
		$headers[] = [ 'message-id', $this->getMessageID() ];
		if ($this->getReferences()) {
			$headers[] = [ 'references', implode(' ', $this->getReferences()) ];
		}
		if ($this->getInReplyTo()) {
			$headers[] = [ 'in-reply-to', implode(' ', $this->getInReplyTo()) ];
		}
		$headers[] = [ 'mime-version', '1.0' ];
		
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
}
