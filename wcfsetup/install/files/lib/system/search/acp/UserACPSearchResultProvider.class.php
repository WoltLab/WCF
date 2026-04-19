<?php

namespace wcf\system\search\acp;

use wcf\acp\form\UserEditForm;
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
    #[\Override]
    public function search(string $query)
    {
        if (!WCF::getSession()->hasPermission('admin.user.canEditUser')) {
            return [];
        }

        $conditionBuilder = new PreparedStatementConditionBuilder(true, 'OR');
        $conditionBuilder->add("username LIKE ?", [[$query . '%']]);

        if (WCF::getSession()->hasPermission('admin.user.canEditMailAddress')) {
            $conditionBuilder->add("email LIKE ?", [[$query . '%']]);
        }

        $sql = "SELECT  *
                FROM    wcf1_user
                {$conditionBuilder}";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        $results = [];

        while ($user = $statement->fetchObject(User::class)) {
            if (UserGroup::isAccessibleGroup($user->getGroupIDs())) {
                $results[] = new ACPSearchResult($user->username, LinkHandler::getInstance()->getControllerLink(UserEditForm::class, [
                    'object' => $user,
                ]));
            }
        }

        return $results;
    }
}
