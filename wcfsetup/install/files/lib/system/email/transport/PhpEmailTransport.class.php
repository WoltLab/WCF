<?php
namespace wcf\system\email\transport;
use wcf\system\email\transport\exception\TransientFailure;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\util\StringUtil;

/**
 * PhpEmailTransport is an implementation of an email transport which sends emails using mail().
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Transport
 * @since	3.0
 */
class PhpEmailTransport implements EmailTransport {
	/**
	 * Delivers the given email via mail().
	 * 
	 * @param	Email		$email
	 * @param	Mailbox		$envelopeFrom
	 * @param	Mailbox		$envelopeTo
	 * @throws	TransientFailure
	 */
	public function deliver(Email $email, Mailbox $envelopeFrom, Mailbox $envelopeTo) {
		$headers = array_filter($email->getHeaders(), function ($item) {
			// filter out headers that are either
			//   a) automatically added by PHP
			//   b) interpreted by sendmail because of -t
			// 
			// The email will be slightly mangled as the result of this. In particular
			// the 'To' and 'Cc' headers will be cleared, which makes this email appear
			// to be sent to a single recipient only.
			// But this is better than crippling the superior transports or special casing
			// the PhpTransport in other classes.
			return $item[0] !== 'subject' && $item[0] !== 'to' && $item[0] !== 'cc' && $item[0] !== 'bcc';
		});
		
		$headers = implode("\r\n", array_map(function ($item) {
			return implode(': ', $item);
		}, $headers));
		
		if (MAIL_USE_F_PARAM) {
			$return = mail($envelopeTo->getAddress(), $email->getSubject(), StringUtil::unifyNewlines($email->getBodyString()), $headers, '-f'.$envelopeFrom->getAddress());
		}
		else {
			$return = mail($envelopeTo->getAddress(), $email->getSubject(), StringUtil::unifyNewlines($email->getBodyString()), $headers);
		}
		
		if (!$return) {
			throw new TransientFailure("mail() returned false");
		}
	}
}
