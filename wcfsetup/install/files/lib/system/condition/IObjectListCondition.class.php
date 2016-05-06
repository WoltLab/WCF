<?php
namespace wcf\system\condition;
use wcf\data\DatabaseObjectList;

/**
 * Every implementation of database object list-related conditions needs to implements
 * this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.condition
 * @category	Community Framework
 * @since	2.2
 */
interface IObjectListCondition extends ICondition {
	/**
	 * Adds a condition to the given object list based on the given condition
	 * data returned by \wcf\system\condition\ICondition::getData().
	 * 
	 * @param	DatabaseObjectList	$objectList
	 * @param	array			$conditionData
	 */
	public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData);
}
