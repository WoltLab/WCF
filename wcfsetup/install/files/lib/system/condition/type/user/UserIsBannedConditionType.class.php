<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserIsBannedConditionType extends AbstractUserBooleanConditionType
{
    public function __construct()
    {
        parent::__construct("isBanned", 'banned');
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        if ($this->filter) {
            $objectList->getConditionBuilder()->add("{$objectList->getDatabaseTableAlias()}.banned = ?", [1]);
        } else {
            $objectList->getConditionBuilder()->add("{$objectList->getDatabaseTableAlias()}.banned = ?", [0]);
        }
    }

    #[\Override]
    public function matches(object $object): bool
    {
        if ($this->filter) {
            return (bool)$object->banned;
        } else {
            return !$object->banned;
        }
    }
}
