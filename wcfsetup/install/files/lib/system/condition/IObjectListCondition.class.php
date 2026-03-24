<?php

namespace wcf\system\condition;

use wcf\data\DatabaseObjectList;

/**
 * Every implementation of database object list-related conditions needs to implements
 * this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @template TDatabaseObjectList of DatabaseObjectList
 */
interface IObjectListCondition extends ICondition
{
    /**
     * Adds a condition to the given object list based on the given condition
     * data returned by \wcf\system\condition\ICondition::getData().
     *
     * @param TDatabaseObjectList $objectList
     * @param mixed[] $conditionData
     * @return void
     * @throws  \InvalidArgumentException   if the given object list object is no object of the expected database object list class
     */
    public function addObjectListCondition(DatabaseObjectList $objectList, array $conditionData);
}
