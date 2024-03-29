<?php

namespace wcf\system\condition;

use wcf\data\DatabaseObject;

/**
 * Every implementation of database object-related conditions needs to implements
 * this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
interface IObjectCondition extends ICondition
{
    /**
     * Returns true if the given object fulfills the condition specified by
     * the given condition data returned by \wcf\system\condition\ICondition::getData().
     *
     * @param DatabaseObject $object
     * @param array $conditionData
     * @return  bool
     */
    public function checkObject(DatabaseObject $object, array $conditionData);
}
