<?php

namespace wcf\system\email\exception;

/**
 * Indicates that the recipient account of the IUserMailbox no longer exists.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class UserDeleted extends \Exception
{
    public static function forUserId(int $userID): self
    {
        return new self(\sprintf(
            "The user account with ID '%d' could not be found.",
            $userID
        ));
    }
}
