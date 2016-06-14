<?php
namespace wcf\system\email\transport;
use wcf\system\email\Email;
use wcf\system\email\Mailbox;

/**
 * An EmailTransport sends emails.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Transport
 * @since	3.0
 */
interface EmailTransport {
	/**
	 * Delivers the given $email to the given Mailbox as the recipient.
	 * 
	 * @param	\wcf\system\email\Email		$email
	 * @param	\wcf\system\email\Mailbox	$envelopeFrom
	 * @param	\wcf\system\email\Mailbox	$envelopeTo
	 */
	public function deliver(Email $email, Mailbox $envelopeFrom, Mailbox $envelopeTo);
}
