<?php
namespace wcf\data\contact\recipient;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit contact recipients.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Recipient
 * @since	3.1
 * 
 * @method static	ContactRecipient	create(array $parameters = [])
 * @method		ContactRecipient	getDecoratedObject()
 * @mixin		ContactRecipient
 */
class ContactRecipientEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ContactRecipient::class;
}
