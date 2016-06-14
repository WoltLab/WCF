<?php
namespace wcf\data\label\group;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\acl\ACLHandler;
use wcf\system\cache\builder\LabelCacheBuilder;

/**
 * Provides functions to edit label groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Label\Group
 * 
 * @method	LabelGroup	getDecoratedObject()
 * @mixin	LabelGroup
 */
class LabelGroupEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = LabelGroup::class;
	
	/**
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
		$count = parent::deleteAll($objectIDs);
		
		// remove ACL values
		$objectTypeID = ACLHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.label');
		ACLHandler::getInstance()->removeValues($objectTypeID, $objectIDs);
		
		return $count;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		LabelCacheBuilder::getInstance()->reset();
	}
}
