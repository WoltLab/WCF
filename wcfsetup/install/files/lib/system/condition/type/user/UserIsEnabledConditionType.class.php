<?php

namespace wcf\system\condition\type\user;

use wcf\data\DatabaseObjectList;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserIsEnabledConditionType extends AbstractUserBooleanConditionType
{
    public function __construct()
    {
        parent::__construct("isEnabled", 'activationCode', 'userIsEnabled', 'com.woltlab.wcf.user.state');
    }

    #[\Override]
    public function applyFilter(DatabaseObjectList $objectList): void
    {
        if ($this->filter) {
            $objectList->getConditionBuilder()->add("{$objectList->getDatabaseTableAlias()}.activationCode = ?", [0]);
        } else {
            $objectList->getConditionBuilder()->add("{$objectList->getDatabaseTableAlias()}.activationCode <> ?", [0]);
        }
    }

    #[\Override]
    public function matches(object $object): bool
    {
        if ($this->filter) {
            return $object->activationCode === 0;
        } else {
            return $object->activationCode !== 0;
        }
    }
}
