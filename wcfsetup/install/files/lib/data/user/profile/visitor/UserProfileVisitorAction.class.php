<?php

namespace wcf\data\user\profile\visitor;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IGroupedUserListAction;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

use wcf\system\exception\UserInputException;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes profile visitor-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserProfileVisitor      create()
 * @method  UserProfileVisitorEditor[]  getObjects()
 * @method  UserProfileVisitorEditor    getSingleObject()
 */
class UserProfileVisitorAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getGroupedUserList'];

    /**
     * user profile object
     * @var UserProfile;
     */
    public $userProfile;

    /**
     * @inheritDoc
     */
    public function validateGetGroupedUserList()
    {
        $this->readInteger('pageNo');
        $this->readInteger('userID');

        $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['userID']);
        if (!$this->userProfile) {
            throw new UserInputException('userID');
        }
        if ($this->userProfile->isProtected()) {
            throw new PermissionDeniedException();
        }

        if ($this->parameters['pageNo'] < 1) {
            throw new UserInputException('pageNo');
        }
    }

    /**
     * @inheritDoc
     */
    public function getGroupedUserList()
    {
        // resolve page count
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_user_profile_visitor
                WHERE   ownerID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->parameters['userID']]);
        $pageCount = \ceil($statement->fetchSingleColumn() / 20);

        // get user ids
        $sql = "SELECT      userID
                FROM        wcf1_user_profile_visitor
                WHERE       ownerID = ?
                ORDER BY    time DESC";
        $statement = WCF::getDB()->prepare($sql, 20, ($this->parameters['pageNo'] - 1) * 20);
        $statement->execute([$this->parameters['userID']]);
        $userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        // create group
        $group = new GroupedUserList();
        $group->addUserIDs($userIDs);

        // load user profiles
        GroupedUserList::loadUsers();

        WCF::getTPL()->assign([
            'groupedUsers' => [$group],
        ]);

        return [
            'pageCount' => $pageCount,
            'template' => WCF::getTPL()->fetch('groupedUserList'),
        ];
    }

    /**
     * Inserts a new visitor if it does not already exist, or updates it if it does.
     * @since       5.2
     */
    public function registerVisitor()
    {
        $sql = "INSERT INTO             wcf1_user_profile_visitor
                                        (ownerID, userID, time)
                VALUES                  (?, ?, ?)
                ON DUPLICATE KEY UPDATE time = VALUES(time)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->parameters['data']['ownerID'],
            $this->parameters['data']['userID'],
            $this->parameters['data']['time'] ?? TIME_NOW,
        ]);
    }
}
