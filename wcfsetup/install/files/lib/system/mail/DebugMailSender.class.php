<?php
namespace wcf\system\mail;
use wcf\system\io\File;

/**
 * DebugMailSender is a debug implementation of mailsender.
 * It writes e-mails in a log file.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.mail
 * @category 	Community Framework
 */
class DebugMailSender extends MailSender {
	protected $log = null;
	
	/**
	 * Writes the given e-mail in a log file.
	 * 
	 * @param	Mail	$mail
	 */
	public function sendMail(Mail $mail) {
		if ($this->log === null) {
			$this->log = new File(MAIL_DEBUG_LOGFILE_PATH.'mail.log', 'ab');
		}
		
		$this->log->write($this->printMail($mail));
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
