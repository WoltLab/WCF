<?php
namespace wcf\data\object\type;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;

/**
 * Provides functions to edit object types.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Object\Type
 * 
 * @method	ObjectType	getDecoratedObject()
 * @mixin	ObjectType
 */
class ObjectTypeEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ObjectType::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		ObjectTypeCache::getInstance()->resetCache();
	}
}
