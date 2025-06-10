<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
abstract class AbstractUserIsNullConditionType extends AbstractUserBooleanConditionType
{
    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        if ($this->filter) {
            $objectList->getConditionBuilder()->add("{$objectList->getDatabaseTableAlias()}.{$this->columnName} IS NOT NULL");
        } else {
            $objectList->getConditionBuilder()->add("{$objectList->getDatabaseTableAlias()}.{$this->columnName} IS NULL");
        }
    }

    #[\Override]
    public function matches(object $object): bool
    {
        if ($this->filter) {
            return $object->{$this->columnName} !== null;
        } else {
            return $object->{$this->columnName} === null;
        }
    }
}
