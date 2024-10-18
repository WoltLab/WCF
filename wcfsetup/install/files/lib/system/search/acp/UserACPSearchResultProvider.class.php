<?php

namespace wcf\system\search\acp;

use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search result provider implementation for users.
 *
 * @author  Joshua Ruesweg, Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserACPSearchResultProvider implements IACPSearchResultProvider
{
    /**
     * @inheritDoc
     */
    public function search($query)
    {
        if (!WCF::getSession()->getPermission('admin.user.canEditUser')) {
            return [];
        }

        $conditionBuilder = new PreparedStatementConditionBuilder(true, 'OR');
        $conditionBuilder->add("username LIKE ?", [[$query . '%']]);

        if (WCF::getSession()->getPermission('admin.user.canEditMailAddress')) {
            $conditionBuilder->add("email LIKE ?", [[$query . '%']]);
        }

        $sql = "SELECT  *
                FROM    wcf1_user
                {$conditionBuilder}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        $results = [];

        /** @var User $user */
        while ($user = $statement->fetchObject(User::class)) {
            if (UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                $results[] = new ACPSearchResult($user->username, LinkHandler::getInstance()->getLink('UserEdit', [
                    'object' => $user,
                ]));
            }
        }

        return $results;
    }
}
