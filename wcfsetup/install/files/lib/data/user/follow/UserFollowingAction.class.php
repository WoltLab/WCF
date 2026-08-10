<?php

namespace wcf\data\user\follow;

use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes following-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserFollowingAction extends UserFollowAction
{
    /**
     * @inheritDoc
     */
    protected $className = UserFollowEditor::class;

    #[\Override]
    /**
     * @deprecated 6.3 No longer in use.
     */
    public function validateGetGroupedUserList()
    {
        $this->readInteger('pageNo');
        $this->readInteger('userID');

        $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['userID']);
        if ($this->userProfile === null) {
            throw new UserInputException('userID');
        }
        if ($this->userProfile->isProtected()) {
            throw new PermissionDeniedException();
        }

        if ($this->parameters['pageNo'] < 1) {
            throw new UserInputException('pageNo');
        }
    }

    #[\Override]
    /**
     * @deprecated 6.3 No longer in use.
     */
    public function getGroupedUserList()
    {
        // resolve page count
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_user_follow
                WHERE   userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->parameters['userID']]);
        $pageCount = (int)\ceil($statement->fetchSingleColumn() / 20);

        // get user ids
        $sql = "SELECT  followUserID
                FROM    wcf1_user_follow
                WHERE   userID = ?";
        $statement = WCF::getDB()->prepare($sql, 20, ($this->parameters['pageNo'] - 1) * 20);
        $statement->execute([$this->parameters['userID']]);
        $userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        // create group
        $group = new GroupedUserList();
        $group->addUserIDs($userIDs);

        // load user profiles
        GroupedUserList::loadUsers();

        return [
            'pageCount' => $pageCount,
            'template' => WCF::getTPL()->render('wcf', 'groupedUserList', [
                'groupedUsers' => [$group],
            ]),
        ];
    }
}
