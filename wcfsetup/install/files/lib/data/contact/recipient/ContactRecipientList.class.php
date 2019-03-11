<?php
namespace wcf\data\contact\recipient;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of contact recipients.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Recipient
 * @since	3.1
 *
 * @method	ContactRecipient		current()
 * @method	ContactRecipient[]		getObjects()
 * @method	ContactRecipient|null		search($objectID)
 * @property	ContactRecipient[]		$objects
 */
class ContactRecipientList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = ContactRecipient::class;
	
	/**
	 * @inheritDoc
	 */
	public $sqlOrderBy = 'showOrder';
}
