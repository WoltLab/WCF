<?php
namespace wcf\data\contact\option;
use wcf\data\custom\option\CustomOptionEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\ContactOptionCacheBuilder;

/**
 * Provides functions to edit contact recipients.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Option
 * @since	3.1
 * 
 * @method static	ContactOption	create(array $parameters = [])
 * @method		ContactOption	getDecoratedObject()
 * @mixin		ContactOption
 */
class ContactOptionEditor extends CustomOptionEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ContactOption::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		ContactOptionCacheBuilder::getInstance()->reset();
	}
}
