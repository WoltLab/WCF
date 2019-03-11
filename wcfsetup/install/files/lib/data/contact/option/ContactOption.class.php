<?php
namespace wcf\data\contact\option;
use wcf\data\custom\option\CustomOption;

/**
 * Represents a contact option.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Option
 * @since	3.1
 */
class ContactOption extends CustomOption {
	/**
	 * @inheritDoc
	 */
	public static function getDatabaseTableAlias() {
		return 'contact_option';
	}
}
