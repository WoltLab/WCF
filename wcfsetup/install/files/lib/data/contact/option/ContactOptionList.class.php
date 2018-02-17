<?php
namespace wcf\data\contact\option;
use wcf\data\custom\option\CustomOptionList;

/**
 * Represents a list of contact recipients.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Option
 * @since	3.1
 *
 * @method	ContactOption		current()
 * @method	ContactOption[]		getObjects()
 * @method	ContactOption|null	search($objectID)
 * @property	ContactOption[]		$objects
 */
class ContactOptionList extends CustomOptionList {
	/**
	 * @inheritDoc
	 */
	public $className = ContactOption::class;
}
