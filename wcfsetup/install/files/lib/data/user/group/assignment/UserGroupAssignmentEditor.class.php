<?php
namespace wcf\data\user\group\assignment;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\cache\builder\UserGroupAssignmentCacheBuilder;

/**
 * Executes user group assignment-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.user.group.assignment
 * @category	Community Framework
 */
class UserGroupAssignmentEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\group\assignment\UserGroupAssignment';
	
	/**
	 * @see	\wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		UserGroupAssignmentCacheBuilder::getInstance()->reset();
		ConditionCacheBuilder::getInstance()->reset(array(
			'definitionID' => ObjectTypeCache::getInstance()->getDefinitionByName('com.woltlab.wcf.condition.userGroupAssignment')->definitionID
		));
	}
}
