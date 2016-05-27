<?php
namespace wcf\system\email\transport;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;
use wcf\util\StringUtil;

/**
 * PhpEmailTransport is an implementation of an email transport which sends emails using mail().
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.transport
 * @category	Community Framework
 * @since	2.2
 */
class PhpEmailTransport implements EmailTransport {
	/**
	 * Delivers the given email via mail().
	 * 
	 * @param	\wcf\system\email\Email		$email
	 * @param	\wcf\system\email\Mailbox	$envelopeTo
	 */
	public function deliver(Email $email, Mailbox $envelopeTo) {
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
		
		mail($envelopeTo->getAddress(), $email->getSubject(), StringUtil::unifyNewlines($email->getBodyString()), $headers, '-f'.$email->getSender()->getAddress());
	}
}
