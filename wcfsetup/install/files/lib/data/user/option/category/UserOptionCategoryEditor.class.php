<?php
namespace wcf\data\user\option\category;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserOptionCacheBuilder;

/**
 * Provides functions to add, edit and delete user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\User\Option\Category
 * 
 * @method	UserOptionCategory	getDecoratedObject()
 * @mixin	UserOptionCategory
 */
class UserOptionCategoryEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = UserOptionCategory::class;
	
	/**
	 * @inheritDoc
	 * @return	UserOptionCategory
	 */
	public static function create(array $parameters = []) {
		// obtain default values
		if (!isset($parameters['packageID'])) $parameters['packageID'] = PACKAGE_ID;
		
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::create($parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		UserOptionCacheBuilder::getInstance()->reset();
	}
}
