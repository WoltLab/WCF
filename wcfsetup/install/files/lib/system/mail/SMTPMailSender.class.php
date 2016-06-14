<?php
namespace wcf\system\mail;
use wcf\system\exception\SystemException;
use wcf\system\io\RemoteFile;
use wcf\util\StringUtil;

/**
 * Sends a Mail with a connection to a smtp server.
 * 
 * @author	Tim Duesterhus, Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Mail
 * @deprecated	The Community Framework < 2.2 mail API is deprecated in favor of \wcf\system\email\*.
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
	protected $recipients = [];
	
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
		$extensions = explode(Mail::$lineEnding, $this->read());
		$this->getSMTPStatus(array_shift($extensions));
		if ($this->statusCode == 250) {
			$extensions = array_map(function($element) {
				return strtolower(substr($element, 4));
			}, $extensions);
			
			if ($this->connection->hasTLSSupport() && in_array('starttls', $extensions)) {
				$this->write('STARTTLS');
				$this->getSMTPStatus();
				
				if ($this->statusCode != 220) {
					throw new SystemException($this->formatError("cannot enable STARTTLS, though '".MAIL_SMTP_HOST.":".MAIL_SMTP_PORT."' advertised it"));
				}
				
				if (!$this->connection->setTLS(true)) {
					throw new SystemException('enabling TLS failed');
				}
				
				// repeat EHLO
				$this->write('EHLO '.$host);
				$extensions = explode(Mail::$lineEnding, $this->read());
				$this->getSMTPStatus(array_shift($extensions));
				
				if ($this->statusCode != 250) {
					throw new SystemException($this->formatError("could not EHLO after enabling STARTTLS at '".MAIL_SMTP_HOST.":".MAIL_SMTP_PORT."'"));
				}
			}
			
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
	 * @inheritDoc
	 */
	public function sendMail(Mail $mail) {
		$this->recipients = [];
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
			$this->abort();
			throw new SystemException($this->formatError("wrong from format '".$mail->getFrom()."'"));
		}
		
		// recipients
		$recipientCounter = 0;
		foreach ($this->recipients as $recipient) {
			$this->write('RCPT TO:<'.$recipient.'>');
			$this->getSMTPStatus();
			if ($this->statusCode != 250 && $this->statusCode != 251) {
				if ($this->statusCode < 550) {
					$this->abort();
					throw new SystemException($this->formatError("wrong recipient format '".$recipient."'"));
				}
				continue;
			}
			$recipientCounter++;
		}
		if (!$recipientCounter) {
			$this->abort();
			return;
		}
		
		// data
		$this->write("DATA");
		$this->getSMTPStatus();
		if ($this->statusCode != 354 && $this->statusCode != 250) {
			$this->abort();
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
		$lines = explode(Mail::$lineEnding, $mail->getBody());
		foreach ($lines as $line) {
			// 4.5.2 Transparency
			// o  Before sending a line of mail text, the SMTP client checks the
			//    first character of the line.  If it is a period, one additional
			//    period is inserted at the beginning of the line.
			if (StringUtil::startsWith($line, '.')) $line = '.'.$line;
			$this->write($line);
		}
		$this->write(".");
		
		$this->getSMTPStatus();
		if ($this->statusCode != 250) {
			$this->abort();
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
	 * Aborts the current process. This is needed in case a new mail should be
	 * sent after a exception has occured
	 */
	protected function abort() {
		$this->write("RSET");
		$this->read(); // read response, but do not care about status here
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
