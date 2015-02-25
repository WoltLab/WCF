<?php
namespace wcf\system\mail;

/**
 * Sends a mail with the php mail function.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.mail
 * @category	Community Framework
 */
class PHPMailSender extends MailSender {
	/**
	 * @see	\wcf\system\mail\MailSender::sendMail()
	 */
	public function sendMail(Mail $mail) {
		if (MAIL_USE_F_PARAM) return @mb_send_mail($mail->getToString(), $mail->getSubject(), $mail->getBody(), $mail->getHeader(), '-f'.MAIL_FROM_ADDRESS);
		else return @mb_send_mail($mail->getToString(), $mail->getSubject(), $mail->getBody(), $mail->getHeader());
	}
}
