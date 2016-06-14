<?php
namespace wcf\data\condition;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of conditions.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Condition
 *
 * @method	Condition		current()
 * @method	Condition[]		getObjects()
 * @method	Condition|null		search($objectID)
 * @property	Condition[]		$objects
 */
class ConditionList extends DatabaseObjectList { }
