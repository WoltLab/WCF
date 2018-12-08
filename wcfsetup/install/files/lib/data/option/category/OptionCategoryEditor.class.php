<?php
namespace wcf\data\option\category;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit option categories.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Option\Category
 * 
 * @method static	OptionCategory		create(array $parameters = [])
 * @method		OptionCategory		getDecoratedObject()
 * @mixin		OptionCategory
 */
class OptionCategoryEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = OptionCategory::class;
}
