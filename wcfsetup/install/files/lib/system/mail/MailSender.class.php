<?php
namespace wcf\system\mail;

/**
 * Mailsender sends emails.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.mail
 * @category	Community Framework
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
