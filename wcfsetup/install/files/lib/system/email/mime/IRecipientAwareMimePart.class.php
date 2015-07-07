<?php
namespace wcf\system\email\mime;
use wcf\system\email\Mailbox;

/**
 * Represents a mime part that can be customized based in the recipient Mailbox.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 */
interface IRecipientAwareMimePart {
	/**
	 * Makes this mime part aware of it's recipient.
	 * Note: `null` is a valid parameter and denotes that this mime part should
	 * not be individualised.
	 * 
	 * @param	\wcf\system\email\Mailbox	$mailbox
	 */
	public function setRecipient(Mailbox $mailbox = null);
}
