<?php
namespace wcf\data\custom\option;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit file options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Custom\Option
 * @since	3.1
 * 
 * @method static	CustomOption	create(array $parameters = [])
 * @method		CustomOption	getDecoratedObject()
 * @mixin		CustomOption
 */
abstract class CustomOptionEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CustomOption::class;
}
