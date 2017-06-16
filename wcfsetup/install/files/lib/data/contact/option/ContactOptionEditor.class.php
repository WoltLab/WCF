<?php
namespace wcf\data\contact\recipient;
use wcf\data\custom\option\CustomOptionEditor;

/**
 * Provides functions to edit contact recipients.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Contact\Option
 * @since	3.1
 * 
 * @method static	ContactOption	create(array $parameters = [])
 * @method		ContactOption	getDecoratedObject()
 * @mixin		ContactOption
 */
class ContactOptionEditor extends CustomOptionEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ContactOption::class;
}
