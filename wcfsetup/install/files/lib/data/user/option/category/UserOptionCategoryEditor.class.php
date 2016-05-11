<?php
namespace wcf\data\user\option\category;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserOptionCacheBuilder;

/**
 * Provides functions to add, edit and delete user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category	Community Framework
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
	 */
	public static function create(array $parameters = []) {
		// obtain default values
		if (!isset($parameters['packageID'])) $parameters['packageID'] = PACKAGE_ID;
		
		return parent::create($parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		UserOptionCacheBuilder::getInstance()->reset();
	}
}
