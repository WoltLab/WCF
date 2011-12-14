<?php
namespace wcf\system\mail;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * This class represents an e-mail.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.mail
 * @category 	Community Framework
 */
class Mail {
	protected $header = '';
	protected $boundary;
	protected $contentType = "text/plain";
	protected $to;
	protected $subject;
	protected $message;
	protected $from;
	protected $cc;
	protected $bcc;
	protected $attachments = array();
	protected $priority;
	protected $body;
	
	public static $crlf = "\n";

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
	 * @param	integer		$priority
	 * @param	string		$header
	 */
	public function __construct($to = '', $subject = '', $message = '', $from = '', $cc = '', $bcc = '', $attachments = array(), $priority = '', $header = '') {
		$this->setBoundary();
		
		if (empty($from)) 	$from 		= array(MAIL_FROM_NAME => MAIL_FROM_ADDRESS);
		if (empty($priority)) 	$priority 	= 3;
			
		$this->setFrom($from);
		$this->setSubject($subject);
		$this->setMessage($message);
		$this->setPriority($priority);
		$this->setHeader($header);
		
		if (!empty($to))	$this->addTo($to);
		if (!empty($cc))	$this->addCC($cc);
		if (!empty($bcc))	$this->addBCC($bcc);
		
		if (count($attachments) > 0) $this->setAttachments($attachments);
	}
	
	/**
	 * Creates a Basic Header for the Mail
	 * Returns this Header to the function which invoke this class
	 * 
	 * @return	string		mail header
	 */
	public function getHeader() {
		if (!empty($this->header)) {
			$this->header = preg_replace('%(\r\n|\r|\n)%', self::$crlf, $this->header);
		}
		
		$this->header .=
			'X-Priority: 3'.self::$crlf
			.'X-Mailer: WoltLab Community Framework Mail Package'.self::$crlf									
			.'MIME-Version: 1.0'.self::$crlf
			.'From: '.$this->getFrom().self::$crlf
			.($this->getCCString() != '' ? 'CC:'.$this->getCCString().self::$crlf : '')
			.($this->getBCCString() != '' ? 'BCC:'.$this->getBCCString().self::$crlf : '');					
			
		if (count($this->getAttachments())) {
			$this->header .= 'Content-Transfer-Encoding: 8bit'.self::$crlf;
			$this->header .= 'Content-Type: multipart/mixed;'.self::$crlf;
			$this->header .= "\tboundary=".'"'.$this->getBoundary().'";'.self::$crlf;
		}
		else {
			$this->header .= 'Content-Transfer-Encoding: 8bit'.self::$crlf;
			$this->header .= 'Content-Type: '.$this->getContentType().'; charset=UTF-8'.self::$crlf;
		}
		
		return $this->header;
	}
	
	/**
	 * Creates the Recipients List (To, CC, BCC) 
	 * Returns this List to the function which invoke this class
	 * 
	 * @param 	boolean		$withTo
	 * @return	string
	 */
	public function getRecipients($withTo = false) {
		$recipients = '';
		if ($withTo && $this->getToString() != '') $recipients .= 'TO:'.$this->getToString().self::$crlf;
		if ($this->getCCString() != '') $recipients .= 'CC:'.$this->getCCString().self::$crlf;
		if ($this->getBCCString() != '') $recipients .= 'BCC:'.$this->getBCCString().self::$crlf;
		return $recipients;	
	}
	
	/**
	 * Creates the Body (Message, Attachments) for the Mail
	 * Returns the created Body to the function which invoke this class
	 * 
	 * @return	string		mail body
	 */
	public function getBody() {
		$counter = 1;
		$this->body = '';

		if (count($this->getAttachments())) {
			// add message
			$this->body 	.= '--'.$this->getBoundary().self::$crlf;
			$this->body 	.= 'Content-Type: '.$this->getContentType().'; charset="UTF-8"'.self::$crlf;
			$this->body 	.= 'Content-Transfer-Encoding: 8bit'.self::$crlf;
			//$this->body 	.= self::$crlf.self::$crlf;
			$this->body 	.= self::$crlf;
			
			// wrap lines after 70 characters
			$this->body	.= wordwrap($this->getMessage(), 70); 
			$this->body 	.= self::$crlf.self::$crlf;
			$this->body 	.= '--'.$this->getBoundary().self::$crlf;
			
			// add attachments
			foreach ($this->getAttachments() as $attachment) {
				$fileName 	= $attachment['name'];
				$path 		= $attachment['path'];
				
				// download file
				if (FileUtil::isURL($path)) {
					$tmpPath = FileUtil::getTemporaryFilename('mailAttachment_');
					if (!@copy($path, $tmpPath)) continue;
					$path = $tmpPath;
				}
				
				// get file contents
				$data = @file_get_contents($path);
				$data = chunk_split(base64_encode($data), 70, self::$crlf);	
				
				$this->body .= 'Content-Type: application/octetstream; name="'.$fileName.'"'.self::$crlf;
				$this->body .= 'Content-Transfer-Encoding: base64'.self::$crlf;
				$this->body .= 'Content-Disposition: attachment; filename="'.$fileName.'"'.self::$crlf.self::$crlf;
				$this->body .= $data.self::$crlf.self::$crlf;
				
				if ($counter < count($this->getAttachments())) $this->body .= '--'.$this->getBoundary().self::$crlf;
				$counter++;
			}
			
			$this->body .= self::$crlf.'--'.$this->getBoundary().'--';
		}
		else {
			//$this->body 	.= self::$crlf;
			$this->body	.= $this->getMessage();
		}
		return $this->body;
	}
	
	/**
	 * Builds a formatted address: "$name" <$email>
	 * 
	 * @param	string		$name
	 * @param	string		$email
	 * @param 	boolean		$encodeName
	 * @return	string
	 */
	public static function buildAddress($name, $email, $encodeName = true) {
		if (!empty($name) && MAIL_USE_FORMATTED_ADDRESS) {
			if ($encodeName) $name = Mail::encodeMIMEHeader($name);
			if (!preg_match('/^[a-z0-9 ]*$/i', $name)) return '"'.str_replace('"', '\"', $name).'" <'.$email.'>';
			else return $name . ' <'.$email.'>';
		}
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
		return preg_replace('%(\r\n|\r|\n)%', self::$crlf, $this->message . (MAIL_SIGNATURE ? self::$crlf . '-- ' . self::$crlf  MAIL_SIGNATURE : ''));
	}
	
	/**
	 * Sets the sender of this mail.
	 * 
	 * @param	mixed		$from
	 */
	public function setFrom($from) {
		if (is_array($from)) {
			$this->from = self::buildAddress(key($from), current($from), false);
		}
		else {
			$this->from = $from;
		}
	}
	
	/**
	 * Gets the sender of this mail.
	 * 
	 * @return	mixed
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
	 * Returns the carbon copy recipients of this mail as String.
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
	 * Returns the blind carbon copy recipients of this mail as String.
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
	 * @param	string		$path
	 */
	public function addAttachment($path) {
		$this->attachments[] = array('path' => $path, 'name' => basename($path));
	}
	
	/**
	 * Sets the Priority of the Mail; Default = 3
	 * 
	 * @param	integer 	$priority
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
	}
	
	/**
	 * Returns the Priority of the Mail
	 * 
	 * @return	integer
	 */
	public function getPriority() {
		return $this->priority;
	}
	
	/**
	 * Creates a boundary for mutlipart/mixed Mail 
	 */
	protected function setBoundary() {
		$this->boundary = "==Multipart_Boundary_x".StringUtil::getRandomID()."x";
	}
	
	/**
	 * Returns the created Boundary
	 * 
	 * @return	string
	 */
	protected function getBoundary() {
		return $this->boundary;
	}
	
	/**
	 * Returns the Content Type
	 * 
	 * @return	string
	 */
	public function getContentType() {
		return $this->contentType;
	}
	
	/**
	 * Sets the content type.
	 * 
	 * @param	string 		$contentType
	 */
	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}
	
	/**
	 * Sets additional headers
	 * 
	 * @param	string		$header
	 */
	public function setHeader($header) {
		if (!empty($header)) {
			$this->header .= $header.self::$crlf;
		}
	}
	
	/**
	 * Encodes string for MIME header.
	 */
	public static function encodeMIMEHeader($string) {
		if (function_exists('mb_encode_mimeheader')) {
			$string = mb_encode_mimeheader($string, 'UTF-8', 'Q', Mail::$crlf);
		}
		else {
			$string = '=?UTF-8?Q?'.preg_replace('/[^\r\n]{73}[^=\r\n]{2}/', "$0=\r\n", str_replace("%", "=", str_replace("%0D%0A", "\r\n", str_replace("%20", " ", rawurlencode($string))))).'?=';
		}
		
		return $string;
	}
}
