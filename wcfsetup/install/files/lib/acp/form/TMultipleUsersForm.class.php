<?php

namespace wcf\acp\form;

use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\exception\IllegalLinkException;
use wcf\util\ArrayUtil;

/**
 * Reads the users a form applies to from the `ids` parameter that is set by
 * the bulk interactions of the user list.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TMultipleUsersForm
{
    /**
     * @return array<int, User>
     * @throws IllegalLinkException if no ids are given or any of the users does not exist
     */
    protected function readUsersFromRequest(): array
    {
        $userIDs = [];
        if (isset($_REQUEST['ids']) && \is_array($_REQUEST['ids'])) {
            $userIDs = \array_unique(ArrayUtil::toIntegerArray($_REQUEST['ids']));
        }

        if ($userIDs === []) {
            throw new IllegalLinkException();
        }

        $userList = new UserList();
        $userList->setObjectIDs($userIDs);
        $userList->readObjects();
        $users = $userList->getObjects();

        if (\count($users) !== \count($userIDs)) {
            throw new IllegalLinkException();
        }

        return $users;
    }
}
