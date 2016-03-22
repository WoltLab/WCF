<?php
namespace wcf\system\email\transport;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;

/**
 * An EmailTransport sends emails.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.transport
 * @category	Community Framework
 * @since	2.2
 */
interface EmailTransport {
	/**
	 * Delivers the given $email to the given Mailbox as the recipient.
	 * 
	 * @param	\wcf\system\email\Email		$email
	 * @param	\wcf\system\email\Mailbox	$envelopeTo
	 */
	public function deliver(Email $email, Mailbox $envelopeTo);
}
