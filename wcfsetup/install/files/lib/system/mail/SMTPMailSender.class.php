<?php
namespace wcf\system\mail;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;

/**
 * Sends a Mail with a connection to a smtp server.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.mail
 * @category 	Community Framework
 */
class SMTPMailSender extends MailSender {
	protected $connection = null;
	protected $statusCode = '';
	protected $statusMsg = '';
	protected $recipients;
	
	/**
	 * Creates a new SMTPMailSender object.
	 */
	public function __construct() {
		Mail::$crlf = "\r\n";
	}
	
	/**
	 * Destroys the SMTPMailSender object.
	 */
	public function __destruct() {
		$this->disconnect();
	}

	/**
	 * Connects to the smtp-server
	 */
	protected function connect() {
		// connect
		$this->connection = new RemoteFile(MAIL_SMTP_HOST, MAIL_SMTP_PORT);
		$this->getSMTPStatus();
		if ($this->statusCode != 220) {
			throw new SystemException($this->formatError("can not connect to '".MAIL_SMTP_HOST.":".MAIL_SMTP_PORT."'"), 17000);
		}
		
		// send ehlo
		$this->write('EHLO '.$_SERVER['HTTP_HOST']);
		$this->getSMTPStatus();
		if ($this->statusCode == 250) {
			// do authentication
			if (MAIL_SMTP_USER != '' || MAIL_SMTP_PASSWORD != '') {
				$this->auth();
			}
		}
		else {
			// send helo
			$this->write('HELO '.$_SERVER['HTTP_HOST']);
			$this->getSMTPStatus();
			if ($this->statusCode != 250) {
				throw new SystemException($this->formatError("can not connect to '".MAIL_SMTP_HOST.":".MAIL_SMTP_PORT."'"), 17000);
			}
		}
	}
	
	/**
	 * Formats a smtp error message.
	 * 
	 * @param	string		$message
	 * @return	string
	 */
	protected function formatError($message) {
		return $message.': '.$this->statusMsg.' ('.$this->statusCode.')';
	}
	
	/**
	 * Does the Authentification of the Client at the Server
	 */
	protected function auth() {
		// Init Authentication
		$this->write('AUTH LOGIN');
		$this->getSMTPStatus();
		
		// checks if auth is supported
		if ($this->statusCode != 334) {
			throw new SystemException($this->formatError("smtp mail server '".MAIL_SMTP_HOST.":".MAIL_SMTP_PORT."' does not support user authentication"), 17001);
		}
		
		// sending user information to smtp-server
		$this->write(base64_encode(MAIL_SMTP_USER));
		$this->getSMTPStatus();
		if ($this->statusCode != 334) {
			throw new SystemException($this->formatError("unknown smtp user '".MAIL_SMTP_USER."'"), 17002);
		}
			
		$this->write(base64_encode(MAIL_SMTP_PASSWORD));
		$this->getSMTPStatus();
		if ($this->statusCode != 235) {
			throw new SystemException($this->formatError("invalid password for smtp user '".MAIL_SMTP_USER."'"), 17003);
		}
	}
	
	/**
	 * @see MailSender::sendMail()
	 */
	public function sendMail(Mail $mail) {
		$this->recipients = array();
		if (count($mail->getTo()) > 0) 	$this->recipients = $mail->getTo();
		if (count($mail->getCC()) > 0) 	$this->recipients = array_merge($this->recipients, $mail->getCC());
		if (count($mail->getBCC())> 0) 	$this->recipients = array_merge($this->recipients, $mail->getBCC());
		
		// apply connection
		if ($this->connection === null) {
			$this->connect();
		}
		
		// send mail
		$this->write('MAIL FROM:<'.$mail->getFrom().'>');
		$this->getSMTPStatus();
		if ($this->statusCode != 250) {
			throw new SystemException($this->formatError("wrong from format '".$mail->getFrom()."'"), 17004);
		}
		
		// recipients
		$recipientCounter = 0;
		foreach ($this->recipients as $recipient) {
			$this->write('RCPT TO:<'.$recipient.'>');
			$this->getSMTPStatus();
			if ($this->statusCode != 250 && $this->statusCode != 251) {
				if ($this->statusCode < 550) {
					throw new SystemException($this->formatError("wrong recipient format '".$recipient."'"), 17004);
				}
				continue;
			}
			$recipientCounter++;
		}
		if (!$recipientCounter) {
			$this->write("RSET");
			return;
		}
		
		// data
		$this->write("DATA");
		$this->getSMTPStatus();
		if ($this->statusCode != 354) {
			throw new SystemException($this->formatError("smtp error"), 17005);
		}
						
		$header =
			"Date: ".gmdate('r').Mail::$crlf
			."To: ".$mail->getToString().Mail::$crlf
			."Message-ID: <".md5(uniqid())."@".$_SERVER['SERVER_NAME'].">".Mail::$crlf
			."Subject: ".Mail::encodeMIMEHeader($mail->getSubject()).Mail::$crlf
			.$mail->getHeader();

		$this->write($header);
		$this->write("");
		$this->write($mail->getBody());
		$this->write(".");
			
		$this->getSMTPStatus();
		if ($this->statusCode != 250) {
			throw new SystemException($this->formatError("message sending failed"), 17005);
		}
	}
	
	/**
	 * Disconnects the Client-Server connection
	 */
	function disconnect() {
		if ($this->connection === null) {
			return;
		}

		$this->write("QUIT");
		$this->read();
		$this->connection->close();
		$this->connection = null;
	}
	
	/**
	 * Reads the Information wich the Server sends back.
	 * 
	 * @return 	string
	 */
	protected function read() {
		$result = '';
		while ($read = $this->connection->gets()) {
			$result .= $read;
			if (substr($read, 3, 1) == " ") break;
		}
		return $result; 
	}
	
	/**
	 * Gets error code and message from a server message.
	 * 
	 * @param	string		$data
	 */
	protected function getSMTPStatus($data = null) {
		if ($data === null) $data = $this->read();
		$this->statusCode = intval(substr($data, 0, 3));
		$this->statusMsg  = substr($data, 4);
	}
	
	/**
	 * Sends Information to the smtp-Server
	 * 
	 * @param	string		$data
	 */
	protected function write($data) {
		$this->connection->puts($data.Mail::$crlf);
	}
}
?>