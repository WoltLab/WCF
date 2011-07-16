<?php
namespace wcf\data\user\option\category;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to add, edit and delete user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.option.category
 * @category 	Community Framework
 */
class UserOptionCategoryEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\user\option\category\UserOptionCategory::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\option\category\UserOptionCategory';
	
	/**
	 * @see	wcf\data\EditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		// obtain default values
		if (!isset($parameters['packageID'])) $parameters['packageID'] = PACKAGE_ID;
		
		return parent::create($parameters);
	}
}
