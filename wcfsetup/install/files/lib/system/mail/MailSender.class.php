<?php
namespace wcf\system\mail;

/**
 * Mailsender sends emails.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Mail
 * @deprecated	The Community Framework < 2.2 mail API is deprecated in favor of \wcf\system\email\*.
 */
abstract class MailSender {
	/**
	 * unique mail server instance
	 * @var	\wcf\system\mail\MailSender
	 */
	protected static $instance = null;
	
	/**
	 * Returns the default mail sender.
	 * 
	 * @return	MailSender
	 */
	public static function getInstance() {
		if (self::$instance === null) {
			switch (MAIL_SEND_METHOD) {
				case 'php':
					self::$instance = new PHPMailSender();
				break;
				
				case 'smtp':
					self::$instance = new SMTPMailSender();
				break;
				
				case 'debug':
					self::$instance = new DebugMailSender();
				break;
			}
		}
		
		return self::$instance;
	}
	
	/**
	 * Sends an e-mail.
	 * 
	 * @param	\wcf\system\mail\Mail	$mail
	 */
	abstract public function sendMail(Mail $mail);
}
