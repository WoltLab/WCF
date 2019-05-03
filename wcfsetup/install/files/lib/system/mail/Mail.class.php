<?php
namespace wcf\system\mail;
use wcf\data\language\Language;
use wcf\system\email\mime\AttachmentMimePart;
use wcf\system\email\mime\MimePartFacade;
use wcf\system\email\mime\PlainTextMimePart;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\system\WCF;

/**
 * This class represents an e-mail.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
	 * mail message
	 * @var	string
	 */
	protected $message = '';
	
	/**
	 * mail attachments
	 * @var	array
	 */
	protected $attachments = [];
	
	/**
	 * mail language
	 * @var	Language
	 */
	protected $language = null;
	
	/**
	 * the underlying email object
	 * @var	Email
	 */
	protected $email = null;
	
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
		if (empty($priority)) $priority = 3;
		
		$this->setFrom($from);
		$this->setSubject($subject);
		$this->setMessage($message);
		$this->setPriority($priority);
		if ($header != '') $this->setHeader($header);
		
		if (!empty($to)) $this->addTo($to);
		if (!empty($cc)) $this->addCC($cc);
		if (!empty($bcc)) $this->addBCC($bcc);
		
		if (!empty($attachments)) $this->setAttachments($attachments);
	}
	
	/**
	 * Returns the underlying email object
	 * 
	 * @return	Email
	 */
	public function getEmail() {
		$attachments = array_map(function ($attachment) {
			return new AttachmentMimePart($attachment['path'], $attachment['name']);
		}, $this->attachments);
		
		$text = new PlainTextMimePart($this->message);
		
		$this->email->setBody(new MimePartFacade([$text], $attachments));
		
		return $this->email;
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getHeader() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getRecipients($withTo = false) {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getBody() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public static function buildAddress($name, $email, $encodeName = true) {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @see Email::send()
	 */
	public function send() {
		$this->getEmail()->send();
	}
	
	protected function makeMailbox($email, $name = null) {
		if ($name) {
			return new Mailbox($email, $name, $this->getLanguage());
		}
		if (preg_match('~^(.+) <([^>]+)>$~', $email, $matches)) {
			return new Mailbox($matches[2], $matches[1], $this->getLanguage());
		}
		return new Mailbox($email, null, $this->getLanguage());
	}
	
	/**
	 * Sets the recipients of this mail.
	 * 
	 * @param	mixed		$to
	 */
	public function addTo($to) {
		if (is_array($to)) {
			foreach ($to as $name => $recipient) {
				$this->email->addRecipient($this->makeMailbox($recipient, $name));
			}
		}
		else {
			$this->email->addRecipient($this->makeMailbox($to));
		}
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getTo() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getToString() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @see Email::setSubject()
	 */
	public function setSubject($subject) {
		$this->email->setSubject($subject);
	}
	
	/**
	 * @see Email::getSubject()
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
		$this->message = $message;
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getMessage() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @see Email::setSender()
	 */
	public function setFrom($from) {
		if (is_array($from)) {
			$this->email->setSender($this->makeMailbox(current($from), key($from)));
		}
		else {
			$this->email->setSender($this->makeMailbox($from));
		}
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getFrom() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * Sets the carbon copy recipients of this mail.
	 * 
	 * @param	mixed		$cc
	 */
	public function addCC($cc) {
		if (is_array($cc)) {
			foreach ($cc as $name => $recipient) {
				$this->email->addRecipient($this->makeMailbox($recipient, $name), 'cc');
			}
		}
		else {
			$this->email->addRecipient($this->makeMailbox($cc), 'cc');
		}
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getCC() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getCCString() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * Sets the blind carbon copy recipients of this mail.
	 * 
	 * @param	mixed		$bcc
	 */
	public function addBCC($bcc) {
		if (is_array($bcc)) {
			foreach ($bcc as $name => $recipient) {
				$this->email->addRecipient($this->makeMailbox($recipient, $name), 'bcc');
			}
		}
		else {
			$this->email->addRecipient($this->makeMailbox($bcc), 'bcc');
		}
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getBCC() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getBCCString() {
		throw new \BadMethodCallException('This method is unavailable.');
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
		$this->email->addHeader('x-priority', $priority);
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getPriority() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function getContentType() {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function setContentType($contentType) {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * @throws \BadMethodCallException always
	 */
	public function setHeader($header) {
		throw new \BadMethodCallException('This method is unavailable.');
	}
	
	/**
	 * Sets the mail language.
	 * 
	 * @param	Language	$language
	 */
	public function setLanguage(Language $language) {
		$this->language = $language;
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
		if (function_exists('mb_encode_mimeheader')) {
			$string = mb_encode_mimeheader($string, 'UTF-8', 'Q', self::$lineEnding);
		}
		else {
			$string = '=?UTF-8?Q?'.preg_replace('/[^\r\n]{73}[^=\r\n]{2}/', "$0=\r\n", str_replace("%", "=", str_replace("%0D%0A", "\r\n", str_replace("%20", " ", rawurlencode($string))))).'?=';
		}
		
		return $string;
	}
}
