<?php
namespace wcf\system\mail;
use wcf\system\io\File;

/**
 * DebugMailSender is a debug implementation of mailsender which writes emails in
 * a log file.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Mail
 * @deprecated	The Community Framework 2.x mail API is deprecated in favor of \wcf\system\email\*.
 */
class DebugMailSender extends MailSender {
	/**
	 * log file
	 * @var	File
	 */
	protected $log = null;
	
	/**
	 * Writes the given e-mail in a log file.
	 * 
	 * @param	Mail	$mail
	 */
	public function sendMail(Mail $mail) {
		if ($this->log === null) {
			$logFilePath = WCF_DIR . 'log/';
			$this->log = new File($logFilePath . 'mail.log', 'ab');
		}
		
		$this->log->write(self::printMail($mail));
	}
	
	/**
	 * Prints the given mail.
	 * 
	 * @param	Mail	$mail
	 * @return	string
	 */
	protected static function printMail(Mail $mail) {
		return	"Date: ".gmdate('r')."\n".
			"To: ".$mail->getToString()."\n".
			"Subject: ".$mail->getSubject()."\n".
			$mail->getHeader()."\n".
			"Attachments: ".print_r($mail->getAttachments(), true)."\n\n".
			$mail->getMessage()."\n\n";
	}
}
