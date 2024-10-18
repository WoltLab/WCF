<?php

namespace wcf\data\user\follow;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IGroupedUserListAction;
use wcf\data\user\User;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\command\Follow;
use wcf\system\user\command\Unfollow;
use wcf\system\user\GroupedUserList;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Executes follower-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserFollow      create()
 * @method  UserFollowEditor[]  getObjects()
 * @method  UserFollowEditor    getSingleObject()
 */
class UserFollowAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction
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
     * Validates given parameters.
     *
     * @deprecated 6.1 use `wcf\action\UserFollowAction` instead
     */
    public function validateFollow()
    {
        $this->readInteger('userID', false, 'data');

        if ($this->parameters['data']['userID'] == WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        // check if current user is ignored by target user
        $sql = "SELECT  ignoreID
                FROM    wcf1_user_ignore
                WHERE   userID = ?
                    AND ignoreUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->parameters['data']['userID'],
            WCF::getUser()->userID,
        ]);

        $ignoreID = $statement->fetchSingleColumn();
        if ($ignoreID !== false) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Follows a user.
     *
     * @return  array
     * @deprecated 6.1 use `wcf\action\UserFollowAction` instead
     */
    public function follow()
    {
        $command = new Follow(WCF::getUser(), new User($this->parameters['data']['userID']));
        $command();

        return [
            'following' => 1,
        ];
    }

    /**
     * @inheritDoc
     * @deprecated 6.1 use `wcf\action\UserFollowAction` instead
     */
    public function validateUnfollow()
    {
        $this->validateFollow();
    }

    /**
     * Stops following a user.
     *
     * @return  array
     * @deprecated 6.1 use `wcf\action\UserFollowAction` instead
     */
    public function unfollow()
    {
        $command = new Unfollow(WCF::getUser(), new User($this->parameters['data']['userID']));
        $command();

        return [
            'following' => 0,
        ];
    }

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        // read objects
        if (empty($this->objects)) {
            $this->readObjects();

            if (empty($this->objects)) {
                throw new UserInputException('objectIDs');
            }
        }

        // validate ownership
        foreach ($this->getObjects() as $follow) {
            if ($follow->userID != WCF::getUser()->userID) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $returnValues = parent::delete();

        $followUserIDs = [];
        foreach ($this->getObjects() as $follow) {
            $followUserIDs[] = $follow->followUserID;
            // remove activity event
            UserActivityEventHandler::getInstance()->removeEvents(
                'com.woltlab.wcf.user.recentActivityEvent.follow',
                [$follow->followUserID]
            );
        }

        // reset storage
        UserStorageHandler::getInstance()->reset($followUserIDs, 'followerUserIDs');
        UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'followingUserIDs');

        return $returnValues;
    }

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
                FROM    wcf1_user_follow
                WHERE   followUserID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->parameters['userID']]);
        $pageCount = \ceil($statement->fetchSingleColumn() / 20);

        // get user ids
        $sql = "SELECT  userID
                FROM    wcf1_user_follow
                WHERE   followUserID = ?";
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
}
