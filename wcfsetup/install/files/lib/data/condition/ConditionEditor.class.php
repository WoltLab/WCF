<?php
namespace wcf\data\condition;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\ConditionCacheBuilder;

/**
 * Executes condition-related actions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Condition
 * 
 * @method	Condition	getDecoratedObject()
 * @mixin	Condition
 */
class ConditionEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Condition::class;
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		ConditionCacheBuilder::getInstance()->reset();
	}
}
