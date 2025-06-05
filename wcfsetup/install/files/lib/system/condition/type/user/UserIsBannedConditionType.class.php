<?php

namespace wcf\system\condition\type\user;

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
}
