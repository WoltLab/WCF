<?php
namespace wcf\system\condition;
use wcf\data\DatabaseObject;

/**
 * Every implementation of database object-related conditions needs to implements
 * this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 * @since	3.0
 */
interface IObjectCondition extends ICondition {
	/**
	 * Returns true if the given object fulfills the condition specified by
	 * the given condition data returned by \wcf\system\condition\ICondition::getData().
	 * 
	 * @param	DatabaseObject	$object
	 * @param	array		$conditionData
	 * @return	boolean
	 */
	public function checkObject(DatabaseObject $object, array $conditionData);
}
