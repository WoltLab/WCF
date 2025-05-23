<?php

namespace wcf\system\condition\type;

use wcf\data\DatabaseObjectList;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @template T of DatabaseObjectList
 */
interface IDatabaseObjectListConditionType extends IConditionType
{
    /**
     * Adds a filter to the given object list.
     *
     * @param T $objectList
     */
    public function applyFilter(DatabaseObjectList $objectList): void;
}
