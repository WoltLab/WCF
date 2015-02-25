<?php
namespace wcf\data\user\option\category;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\UserOptionCacheBuilder;

/**
 * Provides functions to add, edit and delete user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category	Community Framework
 */
class UserOptionCategoryEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	\wcf\data\user\option\category\UserOptionCategory::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\option\category\UserOptionCategory';
	
	/**
	 * @see	\wcf\data\IEditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		// obtain default values
		if (!isset($parameters['packageID'])) $parameters['packageID'] = PACKAGE_ID;
		
		return parent::create($parameters);
	}
	
	/**
	 * @see	\wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		UserOptionCacheBuilder::getInstance()->reset();
	}
}
