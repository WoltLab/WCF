<?php
namespace wcf\system\mail;
use wcf\system\io\File;
use wcf\util\FileUtil;

/**
 * DebugMailSender is a debug implementation of mailsender which writes emails in
 * a log file.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.mail
 * @category	Community Framework
 */
class DebugMailSender extends MailSender {
	/**
	 * log file
	 * @var	\wcf\system\io\File
	 */
	protected $log = null;
	
	/**
	 * Writes the given e-mail in a log file.
	 * 
	 * @param	Mail	$mail
	 */
	public function sendMail(Mail $mail) {
		if ($this->log === null) {
			$logFilePath = '';
			if (MAIL_DEBUG_LOGFILE_PATH) {
				$logFilePath = FileUtil::addTrailingSlash(MAIL_DEBUG_LOGFILE_PATH);
			}
			else {
				$logFilePath = WCF_DIR . 'log/';
			}
			
			$this->log = new File($logFilePath . 'mail.log', 'ab');
		}
		
		$this->log->write($this->printMail($mail));
	}
	
	/**
	 * Prints the given mail.
	 * 
	 * @param	\wcf\system\mail\Mail	$mail
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
