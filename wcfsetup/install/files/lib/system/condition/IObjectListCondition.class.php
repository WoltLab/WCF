<?php
namespace wcf\system\condition;
use wcf\data\DatabaseObjectList;

/**
 * Every implementation of database object list-related conditions needs to implements
 * this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Condition
 * @since	3.0
 */
interface IObjectListCondition extends ICondition {
	/**
	 * Adds a condition to the given object list based on the given condition
	 * data returned by \wcf\system\condition\ICondition::getData().
	 * 
	 * @param	DatabaseObjectList	$objectList
	 * @param	array			$conditionData
	 * @throws	\InvalidArgumentException	if the given object list object is no object of the expected database object list class 
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData);
}
