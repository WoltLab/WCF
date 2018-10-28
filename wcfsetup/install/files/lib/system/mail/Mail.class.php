<?php
namespace wcf\system\mail;
use wcf\data\language\Language;
use wcf\system\email\Email;
use wcf\system\email\EmailGrammar;
use wcf\system\email\Mailbox;
use wcf\system\email\mime\AttachmentMimePart;
use wcf\system\email\mime\HtmlTextMimePart;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\PlainTextMimePart;
use wcf\system\WCF;

/**
 * This class represents an e-mail.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Mail
 * @deprecated	The Community Framework 2.x mail API is deprecated in favor of \wcf\system\email\*.
 */
class Mail {
	/**
	 * line ending string
	 * @var	string
	 */
	public static $lineEnding = "\n";
	
	/**
	 * mail content mime type
	 * @var	string
	 */
	protected $contentType = "text/plain";
	
	/**
	 * mail object this class get's forwarded to
	 * @var Email
	 * @since WSC 3.2.0
	 */
	protected $email = null;
	
	/**
	 * language object for the recipients language setting
	 * @var Language
	 * @since 3.2.0
	 */
	protected $language = null;
	
	/**
	 * mail attachments
	 * @var	array
	 */
	protected $attachments = [];
	
	/**
	 * Creates a new Mail object.
	 * 
	 * @param	string		$to
	 * @param	string		$subject
	 * @param	string		$message
	 * @param	string		$from
	 * @param	string		$cc
	 * @param	string		$bcc
	 * @param	array		$attachments
	 * @param	integer|string	$priority
	 * @param	string		$header
	 */
	public function __construct($to = '', $subject = '', $message = '', $from = '', $cc = '', $bcc = '', $attachments = [], $priority = '', $header = '') {
		$this->email = new Email();
		
		if (empty($from)) $from = [MAIL_FROM_NAME => MAIL_FROM_ADDRESS];
		
		$this->setFrom($from);
		$this->setSubject($subject);
		$this->setMessage($message);
		
		if (!empty($header)) $this->setHeader($header);
		if (!empty($priority)) $this->setPriority($priority);
		
		if (!empty($to)) $this->addTo($to);
		if (!empty($cc)) $this->addCC($cc);
		if (!empty($bcc)) $this->addBCC($bcc);
		
		if (!empty($attachments)) $this->setAttachments($attachments);
	}
	
	/**
	 * Creates and returns a basic header for the email.
	 * 
	 * @return	string		mail header
	 */
	public function getHeader() {
		return $this->email->getHeaderString();
	}
	
	/**
	 * Creates and returns the recipients list (TO, CC, BCC).
	 * 
	 * @param	boolean		$withTo
	 * @return	string
	 */
	public function getRecipients($withTo = false) {
		$recipients = '';
		if ($withTo && $this->getToString() != '') $recipients .= 'TO:'.$this->getToString().self::$lineEnding;
		if ($this->getCCString() != '') $recipients .= 'CC:'.$this->getCCString().self::$lineEnding;
		if ($this->getBCCString() != '') $recipients .= 'BCC:'.$this->getBCCString().self::$lineEnding;
		return $recipients;
	}
	
	/**
	 * Creates and returned the body (Message, Attachments) for the email.
	 * 
	 * @return	string		mail body
	 */
	public function getBody() {
		return $this->email->getBodyString();
	}
	
	/**
	 * Builds a formatted address: "$name" <$email>.
	 * 
	 * @param	string		$name
	 * @param	string		$email
	 * @param	boolean		$encodeName
	 * @return	string
	 */
	public static function buildAddress($name, $email, $encodeName = true) {
		return $email;
	}
	
	/**
	 * Sends this mail.
	 */
	public function send() {
		if (!empty($this->attachmments)) {
			$attachments = array_map(function ($attachment) {
				return new AttachmentMimePart($attachment['path'], $attachment['name']);
			}, $this->attachments);
			$mimePart = $this->email->getBody();
			$this->email->setBody(new MimePartFacade([$mimePart], $attachments));
		}
		
		$this->email->send();
	}
	
	/**
	 * Sets the recipients of this mail.
	 * 
	 * @param	mixed		$to
	 */
	public function addTo($to) {
		$this->addRecipient($to);
	}
	
	/**
	 * Returns the recipients of this mail.
	 * 
	 * @return	mixed
	 */
	public function getTo() {
		$recipients = $this->email->getRecipients();
		$result = [];
		
		foreach ($recipients as $recipient) {
			if ($recipient['type'] == 'to') $result[] = $recipient->getAddress();
		}
		
		return $result;
	}
	
	/**
	 * Returns the list of recipients.
	 * 
	 * @return	string
	 */
	public function getToString() {
		return implode(', ', $this->getTo());
	}
	
	/**
	 * Sets the subject of this mail.
	 * 
	 * @param	string		$subject
	 */
	public function setSubject($subject) {
		$this->email->setSubject($subject);
	}
	
	/**
	 * Returns the subject of this mail.
	 * 
	 * @return	string
	 */
	public function getSubject() {
		return $this->email->getSubject();
	}
	
	/**
	 * Sets the message of this mail.
	 * 
	 * @param	string		$message
	 */
	public function setMessage($message) {
		if ($this->contentType == 'text/html') $this->email->setBody(new HtmlTextMimePart($message));
		else $this->email->setBody(new PlainTextMimePart($message));
	}
	
	/**
	 * Returns the message of this mail.
	 * 
	 * @return	string
	 */
	public function getMessage() {
		return $this->email->getBody()->getContent();
	}
	
	/**
	 * Sets the sender of this mail.
	 * 
	 * @param	mixed		$from
	 */
	public function setFrom($from) {
		if (is_array($from)) {
			$this->email->setSender(new Mailbox(current($from), key($from)));
		}
		else {
			$this->email->setSender(new Mailbox($from));
		}
	}
	
	/**
	 * Adds recipients as to, cc or bcc to this mail.
	 *
	 * @param	mixed		$recipient
	 * @param	string		$type
	 * @since 3.2.0
	 */
	protected function addRecipient($recipient, $type = 'to') {
		if (is_array($recipient)) {
			$this->email->addRecipient(new Mailbox(current($recipient), key($recipient), $this->language), $type);
		}
		else {
			$this->email->addRecipient(new Mailbox($recipient, null, $this->language), $type);
		}
	}
	
	/**
	 * Returns the sender of this mail.
	 * 
	 * @return	string
	 */
	public function getFrom() {
		return $this->email->getSender()->getAddress();
	}
	
	/**
	 * Sets the carbon copy recipients of this mail.
	 * 
	 * @param	mixed		$cc
	 */
	public function addCC($cc) {
		$this->addRecipient($cc, 'cc');
	}
	
	/**
	 * Returns the carbon copy recipients of this mail.
	 * 
	 * @return	mixed
	 */
	public function getCC() {
		$recipients = $this->email->getRecipients();
		$result = [];
		
		foreach ($recipients as $recipient) {
			if ($recipient['type'] == 'cc') $result[] = $recipient->getAddress();
		}
		
		return $result;
	}
	
	/**
	 * Returns the carbon copy recipients of this mail as string.
	 * 
	 * @return	string
	 */
	public function getCCString() {
		return implode(', ', $this->getCC());
	}
	
	/**
	 * Sets the blind carbon copy recipients of this mail.
	 * 
	 * @param	mixed		$bcc
	 */
	public function addBCC($bcc) {
		$this->addRecipient($bcc, 'bcc');
	}
	
	/**
	 * Returns the blind carbon copy recipients of this mail.
	 * 
	 * @return	mixed
	 */
	public function getBCC() {
		$recipients = $this->email->getRecipients();
		$result = [];
		
		foreach ($recipients as $recipient) {
			if ($recipient['type'] == 'bcc') $result[] = $recipient->getAddress();
		}
		
		return $result;
	}
	
	/**
	 * Returns the blind carbon copy recipients of this mail as string.
	 * 
	 * @return	string
	 */
	public function getBCCString() {
		return implode(', ', $this->getBCC());
	}
	
	/**
	 * Sets the attachments of this mail.
	 * 
	 * @param	array		$attachments
	 */
	public function setAttachments($attachments) {
		$this->attachments = $attachments;
	}
	
	/**
	 * Returns the attachments of this mail.
	 * 
	 * @return	array
	 */
	public function getAttachments() {
		return $this->attachments;
	}
	
	/**
	 * Adds an attachment to this mail.
	 * 
	 * @param	string		$path		local path to file
	 * @param	string		$name		filename
	 */
	public function addAttachment($path, $name = '') {
		$this->attachments[] = ['path' => $path, 'name' => $name ?: basename($path)];
	}
	
	/**
	 * Sets the priority of the mail.
	 * 
	 * @param	integer		$priority
	 */
	public function setPriority($priority) {
		$this->email->addHeader('X-Priority', $priority);
	}
	
	/**
	 * Returns the priority of the mail
	 * 
	 * @return	integer
	 */
	public function getPriority() {
		$headers = $this->email->getHeaders();
		foreach ($headers as $header) {
			if ($header[0] == 'x-priority') return $header[1];
		}
		
		return 3;
	}
	
	/**
	 * Returns the content type.
	 * 
	 * @return	string
	 */
	public function getContentType() {
		return $this->email->getBody()->getContentType();
	}
	
	/**
	 * Sets the content type.
	 * 
	 * @param	string		$contentType
	 */
	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}
	
	/**
	 * Sets an additional header.
	 * 
	 * @param	string		$header
	 */
	public function setHeader($header) {
		$header = explode(':', $header);
		
		if (count($header) == 2) {
			$this->email->addHeader($header[0], $header[1]);
		}
		else if (count($header) > 2) {
			$name = $header[0];
			unset($header[0]);
			$value = implode(':', $header);
			$this->email->addHeader($name, $value);
		}
		else {
			$this->email->addHeader($header[0], '');
		}
	}
	
	/**
	 * Sets the mail language.
	 * 
	 * @param	Language	$language
	 */
	public function setLanguage(Language $language) {
		$this->language = $language;
		
		$recipients = $this->email->getRecipients();
		if (!empty($recipients)) {
			foreach ($recipients as $recipient) {
				// workaround since there is currently no way to set the language of a mailbox
				$this->email->removeRecipient($recipient);
				$this->email->addRecipient(new Mailbox($recipient->getAddress(), $recipient->getName()));
			}
		}
	}
	
	/**
	 * Returns the mail language.
	 * 
	 * @return	Language
	 */
	public function getLanguage() {
		if ($this->language === null) return WCF::getLanguage();
		
		return $this->language;
	}
	
	/**
	 * Encodes string for MIME header.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public static function encodeMIMEHeader($string) {
		return EmailGrammar::encodeQuotedPrintableHeader($string);
	}
}
