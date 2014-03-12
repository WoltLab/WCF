<?php
namespace wcf\system\mail;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;

/**
 * Sends a Mail with a connection to a smtp server.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.mail
 * @category	Community Framework
 */
class SMTPMailSender extends MailSender {
	/**
	 * smtp connection
	 * @var	\wcf\system\io\RemoteFile
	 */
	protected $connection = null;
	
	/**
	 * last received status code
	 * @var	string
	 */
	protected $statusCode = '';
	
	/**
	 * last received status message
	 * @var	string
	 */
	protected $statusMsg = '';
	
	/**
	 * mail recipients
	 * @var	array
	 */
	protected $recipients = array();
	
	/**
	 * Creates a new SMTPMailSender object.
	 */
	public function __construct() {
		Mail::$lineEnding = "\r\n";
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
			throw new SystemException($this->formatError("can not connect to '".MAIL_SMTP_HOST.":".MAIL_SMTP_PORT."'"));
		}
		
		$host = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '';
		if (empty($host)) {
			$host = gethostname();
			if ($host === false) {
				$host = 'localhost';
			}
		}
		
		// send ehlo
		$this->write('EHLO '.$host);
		$this->getSMTPStatus();
		if ($this->statusCode == 250) {
			// do authentication
			if (MAIL_SMTP_USER != '' || MAIL_SMTP_PASSWORD != '') {
				$this->auth();
			}
		}
		else {
			// send helo
			$this->write('HELO '.$host);
			$this->getSMTPStatus();
			if ($this->statusCode != 250) {
				throw new SystemException($this->formatError("can not connect to '".MAIL_SMTP_HOST.":".MAIL_SMTP_PORT."'"));
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
	 * Does the authentification of the client on the server
	 */
	protected function auth() {
		// init authentication
		$this->write('AUTH LOGIN');
		$this->getSMTPStatus();
		
		// checks if auth is supported
		if ($this->statusCode != 334) {
			throw new SystemException($this->formatError("smtp mail server '".MAIL_SMTP_HOST.":".MAIL_SMTP_PORT."' does not support user authentication"));
		}
		
		// sending user information to smtp-server
		$this->write(base64_encode(MAIL_SMTP_USER));
		$this->getSMTPStatus();
		if ($this->statusCode != 334) {
			throw new SystemException($this->formatError("unknown smtp user '".MAIL_SMTP_USER."'"));
		}
		
		$this->write(base64_encode(MAIL_SMTP_PASSWORD));
		$this->getSMTPStatus();
		if ($this->statusCode != 235) {
			throw new SystemException($this->formatError("invalid password for smtp user '".MAIL_SMTP_USER."'"));
		}
	}
	
	/**
	 * @see	\wcf\system\mail\MailSender::sendMail()
	 */
	public function sendMail(Mail $mail) {
		$this->recipients = array();
		if (count($mail->getTo()) > 0) $this->recipients = $mail->getTo();
		if (count($mail->getCC()) > 0) $this->recipients = array_merge($this->recipients, $mail->getCC());
		if (count($mail->getBCC())> 0) $this->recipients = array_merge($this->recipients, $mail->getBCC());
		
		// apply connection
		if ($this->connection === null) {
			$this->connect();
		}
		
		// send mail
		$this->write('MAIL FROM:<'.$mail->getFrom().'>');
		$this->getSMTPStatus();
		if ($this->statusCode != 250) {
			throw new SystemException($this->formatError("wrong from format '".$mail->getFrom()."'"));
		}
		
		// recipients
		$recipientCounter = 0;
		foreach ($this->recipients as $recipient) {
			$this->write('RCPT TO:<'.$recipient.'>');
			$this->getSMTPStatus();
			if ($this->statusCode != 250 && $this->statusCode != 251) {
				if ($this->statusCode < 550) {
					throw new SystemException($this->formatError("wrong recipient format '".$recipient."'"));
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
		if ($this->statusCode != 354 && $this->statusCode != 250) {
			throw new SystemException($this->formatError("smtp error"));
		}
		
		$serverName = (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : '';
		if (empty($serverName)) {
			$serverName = gethostname();
			if ($serverName === false) {
				$serverName = 'localhost';
			}
		}
		
		$header =
			"Date: ".gmdate('r').Mail::$lineEnding
			."To: ".$mail->getToString().Mail::$lineEnding
			."Message-ID: <".md5(uniqid())."@".$serverName.">".Mail::$lineEnding
			."Subject: ".Mail::encodeMIMEHeader($mail->getSubject()).Mail::$lineEnding
			.$mail->getHeader();
		
		$this->write($header);
		$this->write("");
		$this->write($mail->getBody());
		$this->write(".");
		
		$this->getSMTPStatus();
		if ($this->statusCode != 250) {
			throw new SystemException($this->formatError("message sending failed"));
		}
	}
	
	/**
	 * Disconnects the Client-Server connection
	 */
	public function disconnect() {
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
	 * @return	string
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
		$this->statusMsg = substr($data, 4);
	}
	
	/**
	 * Sends Information to the smtp-Server
	 * 
	 * @param	string		$data
	 */
	protected function write($data) {
		$this->connection->puts($data.Mail::$lineEnding);
	}
}
