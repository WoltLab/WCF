<?php

namespace wcf\system\condition\type\user;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserIsEmailConfirmedConditionType extends AbstractUserIsNullConditionType
{
    public function __construct()
    {
        parent::__construct("isEmailConfirmed", 'emailConfirmed');
    }
}
