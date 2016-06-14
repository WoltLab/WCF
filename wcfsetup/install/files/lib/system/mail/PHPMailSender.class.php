<?php
namespace wcf\system\mail;

/**
 * Sends a mail with the php mail function.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Mail
 * @deprecated	The Community Framework < 2.2 mail API is deprecated in favor of \wcf\system\email\*.
 */
class PHPMailSender extends MailSender {
	/**
	 * @inheritDoc
	 */
	public function sendMail(Mail $mail) {
		if (MAIL_USE_F_PARAM) return @mb_send_mail($mail->getToString(), $mail->getSubject(), $mail->getBody(), $mail->getHeader(), '-f'.MAIL_FROM_ADDRESS);
		else return @mb_send_mail($mail->getToString(), $mail->getSubject(), $mail->getBody(), $mail->getHeader());
	}
}
