<?php
namespace wcf\system\mail;
use wcf\data\language\Language;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * This class represents an e-mail.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Mail
 * @deprecated	The Community Framework < 2.2 mail API is deprecated in favor of \wcf\system\email\*.
 */
class Mail {
	/**
	 * line ending string
	 * @var	string
	 */
	public static $lineEnding = "\n";
	
	/**
	 * mail header
	 * @var	string
	 */
	protected $header = '';
	
	/**
	 * boundary for multipart/mixed mail
	 * @var	string
	 */
	protected $boundary = '';
	
	/**
	 * mail content mime type
	 * @var	string
	 */
	protected $contentType = "text/plain";
	
	/**
	 * mail recipients
	 * @var	string[]
	 */
	protected $to = [];
	
	/**
	 * mail subject
	 * @var	string
	 */
	protected $subject = '';
	
	/**
	 * mail message
	 * @var	string
	 */
	protected $message = '';
	
	/**
	 * mail sender
	 * @var	string
	 */
	protected $from = '';
	
	/**
	 * mail carbon copy
	 * @var	string[]
	 */
	protected $cc = [];
	
	/**
	 * mail blind carbon copy
	 * @var	string[]
	 */
	protected $bcc = [];
	
	/**
	 * mail attachments
	 * @var	array
	 */
	protected $attachments = [];
	
	/**
	 * priority of the mail
	 * @var	integer
	 */
	protected $priority = 3;
	
	/**
	 * mail body
	 * @var	string
	 */
	protected $body = '';
	
	/**
	 * mail language
	 * @var	\wcf\data\language\Language
	 */
	protected $language = null;
	
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
		$this->setBoundary();
		
		if (empty($from)) $from = [MAIL_FROM_NAME => MAIL_FROM_ADDRESS];
		if (empty($priority)) $priority = 3;
		
		$this->setFrom($from);
		$this->setSubject($subject);
		$this->setMessage($message);
		$this->setPriority($priority);
		$this->setHeader($header);
		
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
		if (!empty($this->header)) {
			$this->header = preg_replace('%(\r\n|\r|\n)%', self::$lineEnding, $this->header);
		}
		
		$this->header .=
			'X-Priority: 3'.self::$lineEnding
			.'X-Mailer: WoltLab Suite Mail Package'.self::$lineEnding
			.'From: '.$this->getFrom().self::$lineEnding
			.($this->getCCString() != '' ? 'CC:'.$this->getCCString().self::$lineEnding : '')
			.($this->getBCCString() != '' ? 'BCC:'.$this->getBCCString().self::$lineEnding : '');
			
		if (count($this->getAttachments())) {
			$this->header .= 'Content-Transfer-Encoding: 8bit'.self::$lineEnding;
			$this->header .= 'Content-Type: multipart/mixed;'.self::$lineEnding;
			$this->header .= "\tboundary=".'"'.$this->getBoundary().'";'.self::$lineEnding;
		}
		else {
			$this->header .= 'Content-Transfer-Encoding: 8bit'.self::$lineEnding;
			$this->header .= 'Content-Type: '.$this->getContentType().'; charset=UTF-8'.self::$lineEnding;
		}
		
		$this->header .= 'MIME-Version: 1.0'.self::$lineEnding;
		
		return $this->header;
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
		$counter = 1;
		$this->body = '';
		
		if (count($this->getAttachments())) {
			// add message
			$this->body .= '--'.$this->getBoundary().self::$lineEnding;
			$this->body .= 'Content-Type: '.$this->getContentType().'; charset="UTF-8"'.self::$lineEnding;
			$this->body .= 'Content-Transfer-Encoding: 8bit'.self::$lineEnding;
			$this->body .= self::$lineEnding;
			
			// wrap lines after 70 characters
			$this->body .= wordwrap($this->getMessage(), 70);
			$this->body .= self::$lineEnding.self::$lineEnding;
			$this->body .= '--'.$this->getBoundary().self::$lineEnding;
			
			// add attachments
			foreach ($this->getAttachments() as $attachment) {
				$fileName = $attachment['name'];
				$path = $attachment['path'];
				
				// download file
				if (FileUtil::isURL($path)) {
					$tmpPath = FileUtil::getTemporaryFilename('mailAttachment_');
					if (!@copy($path, $tmpPath)) continue;
					$path = $tmpPath;
				}
				
				// get file contents
				$data = @file_get_contents($path);
				$data = chunk_split(base64_encode($data), 70, self::$lineEnding);
				
				$this->body .= 'Content-Type: application/octetstream; name="'.$fileName.'"'.self::$lineEnding;
				$this->body .= 'Content-Transfer-Encoding: base64'.self::$lineEnding;
				$this->body .= 'Content-Disposition: attachment; filename="'.$fileName.'"'.self::$lineEnding.self::$lineEnding;
				$this->body .= $data.self::$lineEnding.self::$lineEnding;
				
				if ($counter < count($this->getAttachments())) $this->body .= '--'.$this->getBoundary().self::$lineEnding;
				$counter++;
			}
			
			$this->body .= self::$lineEnding.'--'.$this->getBoundary().'--';
		}
		else {
			$this->body .= $this->getMessage();
		}
		return $this->body;
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
		MailSender::getInstance()->sendMail($this);
	}
	
	/**
	 * Sets the recpients of this mail.
	 * 
	 * @param	mixed		$to
	 */
	public function addTo($to) {
		if (is_array($to)) {
			foreach ($to as $name => $recipient) {
				$this->to[] = self::buildAddress($name, $recipient);
			}
		}
		else {
			$this->to[] = $to;
		}
	}
	
	/**
	 * Returns the recipients of this mail.
	 * 
	 * @return	mixed
	 */
	public function getTo() {
		return $this->to;
	}
	
	/**
	 * Returns the list of recipients.
	 * 
	 * @return	string
	 */
	public function getToString() {
		return implode(', ', $this->to);
	}
	
	/**
	 * Sets the subject of this mail.
	 * 
	 * @param	string		$subject
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	/**
	 * Returns the subject of this mail.
	 * 
	 * @return	string
	 */
	public function getSubject() {
		return $this->subject;
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
	 * Returns the message of this mail.
	 * 
	 * @return	string
	 */
	public function getMessage() {
		return preg_replace('%(\r\n|\r|\n)%', self::$lineEnding, $this->message . (MAIL_SIGNATURE ? self::$lineEnding . self::$lineEnding . '-- ' . self::$lineEnding . $this->getLanguage()->get(MAIL_SIGNATURE) : ''));
	}
	
	/**
	 * Sets the sender of this mail.
	 * 
	 * @param	mixed		$from
	 */
	public function setFrom($from) {
		if (is_array($from)) {
			$this->from = self::buildAddress(key($from), current($from));
		}
		else {
			$this->from = $from;
		}
	}
	
	/**
	 * Gets the sender of this mail.
	 * 
	 * @return	string
	 */
	public function getFrom() {
		return $this->from;
	}
	
	/**
	 * Sets the carbon copy recipients of this mail.
	 * 
	 * @param	mixed		$cc
	 */
	public function addCC($cc) {
		if (is_array($cc)) {
			foreach ($cc as $name => $recipient) {
				$this->cc[] = self::buildAddress($name, $recipient);
			}
		}
		else {
			$this->cc[] = $cc;
		}
	}
	
	/**
	 * Returns the carbon copy recipients of this mail.
	 * 
	 * @return	mixed
	 */
	public function getCC() {
		return $this->cc;
	}
	
	/**
	 * Returns the carbon copy recipients of this mail as string.
	 * 
	 * @return	string
	 */
	public function getCCString() {
		return implode(', ', $this->cc);
	}
	
	/**
	 * Sets the blind carbon copy recipients of this mail.
	 * 
	 * @param	mixed		$bcc
	 */
	public function addBCC($bcc) {
		if (is_array($bcc)) {
			foreach ($bcc as $name => $recipient) {
				$this->bcc[] = self::buildAddress($name, $recipient);
			}
		}
		else {
			$this->bcc[] = $bcc;
		}
	}
	
	/**
	 * Returns the blind carbon copy recipients of this mail.
	 * 
	 * @return	mixed
	 */
	public function getBCC() {
		return $this->bcc;
	}
	
	/**
	 * Returns the blind carbon copy recipients of this mail as string.
	 * 
	 * @return	string
	 */
	public function getBCCString() {
		return implode(', ', $this->bcc);
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
		$this->attachments[] = ['path' => $path, 'name' => ($name ?: basename($path))];
	}
	
	/**
	 * Sets the priority of the mail.
	 * 
	 * @param	integer		$priority
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
	}
	
	/**
	 * Returns the priority of the mail
	 * 
	 * @return	integer
	 */
	public function getPriority() {
		return $this->priority;
	}
	
	/**
	 * Creates a boundary for multipart/mixed mail.
	 */
	protected function setBoundary() {
		$this->boundary = "==Multipart_Boundary_x".StringUtil::getRandomID()."x";
	}
	
	/**
	 * Returns the created boundary.
	 * 
	 * @return	string
	 */
	protected function getBoundary() {
		return $this->boundary;
	}
	
	/**
	 * Returns the content type.
	 * 
	 * @return	string
	 */
	public function getContentType() {
		return $this->contentType;
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
		if (!empty($header)) {
			$this->header .= $header.self::$lineEnding;
		}
	}
	
	/**
	 * Sets the mail language.
	 * 
	 * @param	\wcf\data\language\Language	$language
	 */
	public function setLanguage(Language $language) {
		$this->language = $language;
	}
	
	/**
	 * Returns the mail language.
	 * 
	 * @return	\wcf\data\language\Language
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
