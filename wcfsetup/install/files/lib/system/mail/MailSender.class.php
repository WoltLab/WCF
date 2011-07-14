<?php
namespace wcf\system\mail;

/**
 * Mailsender sends e-mails.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.mail
 * @category 	Community Framework
 */
abstract class MailSender {
	/**
	 * unique mail server instance
	 * @var MailSender
	 */
	protected static $instance = null;
	
	/**
	 * Returns the default mail sender.
	 * 
	 * @return	MailSender
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			switch (MAIL_SEND_METHOD) {
				case 'php':
					self::$defaultMailSender = new PHPMailSender();
					break;
				
				case 'smtp':
					self::$defaultMailSender = new SMTPMailSender();
					break;
				
				case 'debug':
					self::$defaultMailSender = new DebugMailSender();
					break;
			}
		}
		
		return self::$instance;
	}
	
	/**
	 * Sends an e-mail.
	 * 
	 * @param	Mail	$mail
	 */
	public abstract function sendMail(Mail $mail); 
}
