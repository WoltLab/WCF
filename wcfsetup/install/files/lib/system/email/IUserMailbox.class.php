<?php

namespace wcf\system\email;

use wcf\data\user\User;

/**
 * Represents mailbox belonging to a specific registered user.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Email
 * @since   5.4
 */
interface IUserMailbox
{
    /**
     * Returns the User object belonging to this Mailbox.
     */
    public function getUser(): User;
}
