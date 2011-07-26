<?php
namespace wcf\system\mail;
use wcf\system\SingletonFactory;

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
abstract class MailSender extends SingletonFactory {
	/**
	 * @see	wcf\system\SingletonFactory::prepareInitialization()
	 */
	protected static function prepareInitialization($className) {
		switch(MAIL_SEND_METHOD) {
			case 'php':
				return 'wcf\system\mail\PHPMailSender';
				break;
			case 'smtp':
				return 'wcf\system\mail\SMTPMailSender';
				break;
			case 'debug':
				return 'wcf\system\mail\DebugMailSender';
				break;
		}
	}
	
	/**
	 * Sends an e-mail.
	 * 
	 * @param	Mail	$mail
	 */
	public abstract function sendMail(Mail $mail); 
}
