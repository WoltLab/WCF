<?php

namespace wcf\data\like;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IGroupedUserListAction;
use wcf\data\reaction\ReactionAction;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\like\LikeHandler;
use wcf\system\reaction\ReactionHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\GroupedUserList;
use wcf\system\WCF;

/**
 * Executes like-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  since 5.2, use \wcf\data\reaction\ReactionAction instead
 *
 * @method  Like        create()
 * @method  LikeEditor[]    getObjects()
 * @method  LikeEditor  getSingleObject()
 */
class LikeAction extends AbstractDatabaseObjectAction implements IGroupedUserListAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getGroupedUserList', 'getLikeDetails', 'load'];

    /**
     * @inheritDoc
     */
    protected $className = LikeEditor::class;

    /**
     * likeable object
     * @var \wcf\data\like\object\ILikeObject
     */
    public $likeableObject;

    /**
     * object type object
     * @var \wcf\data\object\type\ObjectType
     */
    public $objectType;

    /**
     * like object type provider object
     * @var ILikeObjectTypeProvider
     */
    public $objectTypeProvider;

    /**
     * Validates parameters to fetch like details.
     */
    public function validateGetLikeDetails()
    {
        $this->validateObjectParameters();
    }

    /**
     * Returns like details.
     *
     * @return  string[]
     */
    public function getLikeDetails()
    {
        $sql = "SELECT      userID, likeValue
                FROM        wcf1_like
                WHERE       objectID = ?
                        AND objectTypeID = ?
                ORDER BY    time DESC";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->parameters['data']['objectID'],
            $this->objectType->objectTypeID,
        ]);
        $data = [
            Like::LIKE => [],
            Like::DISLIKE => [],
        ];
        while ($row = $statement->fetchArray()) {
            $data[$row['likeValue']][] = $row['userID'];
        }

        $values = [];
        if (!empty($data[Like::LIKE])) {
            $values[Like::LIKE] = new GroupedUserList(WCF::getLanguage()->get('wcf.like.details.like'));
            /** @noinspection PhpUndefinedMethodInspection */
            $values[Like::LIKE]->addUserIDs($data[Like::LIKE]);
        }
        if (!empty($data[Like::DISLIKE])) {
            $values[Like::DISLIKE] = new GroupedUserList(WCF::getLanguage()->get('wcf.like.details.dislike'));
            /** @noinspection PhpUndefinedMethodInspection */
            $values[Like::DISLIKE]->addUserIDs($data[Like::DISLIKE]);
        }

        // load user profiles
        GroupedUserList::loadUsers();

        WCF::getTPL()->assign([
            'groupedUsers' => $values,
        ]);

        return [
            'containerID' => $this->parameters['data']['containerID'],
            'template' => WCF::getTPL()->fetch('groupedUserList'),
        ];
    }

    /**
     * Validates parameters for like-related actions.
     */
    public function validateLike()
    {
        $this->validateObjectParameters();

        // check permissions
        if (!WCF::getUser()->userID || !WCF::getSession()->getPermission('user.like.canLike')) {
            throw new PermissionDeniedException();
        }

        // check if liking own content but forbidden by configuration
        $this->likeableObject = $this->objectTypeProvider->getObjectByID($this->parameters['data']['objectID']);
        $this->likeableObject->setObjectType($this->objectType);
        if ($this->likeableObject->getUserID() == WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function like()
    {
        return $this->updateLike(Like::LIKE);
    }

    /**
     * @inheritDoc
     */
    public function validateDislike()
    {
        // No longer supported since 5.2.
        throw new PermissionDeniedException();
    }

    /**
     * @inheritDoc
     */
    public function dislike()
    {
        return $this->updateLike(Like::DISLIKE);
    }

    /**
     * Sets like/dislike for an object, executing this method again with the same parameters
     * will revert the status (removing like/dislike).
     *
     * @param int $likeValue
     * @return  array
     */
    protected function updateLike($likeValue)
    {
        $likeData = LikeHandler::getInstance()->like($this->likeableObject, WCF::getUser(), $likeValue);

        // handle activity event
        if (UserActivityEventHandler::getInstance()->getObjectTypeID($this->objectType->objectType . '.recentActivityEvent')) {
            if ($likeData['data']['liked'] == 1) {
                UserActivityEventHandler::getInstance()->fireEvent(
                    $this->objectType->objectType . '.recentActivityEvent',
                    $this->parameters['data']['objectID'],
                    $this->likeableObject->getLanguageID()
                );
            } else {
                UserActivityEventHandler::getInstance()->removeEvent(
                    $this->objectType->objectType . '.recentActivityEvent',
                    $this->parameters['data']['objectID']
                );
            }
        }

        // get stats
        return [
            'likes' => ($likeData['data']['likes'] === null) ? 0 : $likeData['data']['likes'],
            'dislikes' => ($likeData['data']['dislikes'] === null) ? 0 : $likeData['data']['dislikes'],
            'cumulativeLikes' => ($likeData['data']['cumulativeLikes'] === null) ? 0 : $likeData['data']['cumulativeLikes'],
            'isLiked' => ($likeData['data']['liked'] == 1) ? 1 : 0,
            'isDisliked' => ($likeData['data']['liked'] == -1) ? 1 : 0,
            'containerID' => $this->parameters['data']['containerID'],
            'newValue' => $likeData['newValue'],
            'oldValue' => $likeData['oldValue'],
            'users' => $likeData['users'],
        ];
    }

    /**
     * Validates permissions for given object.
     */
    protected function validateObjectParameters()
    {
        if (!MODULE_LIKE) {
            throw new PermissionDeniedException();
        }

        $this->readString('containerID', false, 'data');
        $this->readInteger('objectID', false, 'data');
        $this->readString('objectType', false, 'data');

        $this->objectType = ReactionHandler::getInstance()->getObjectType($this->parameters['data']['objectType']);
        if ($this->objectType === null) {
            throw new UserInputException('objectType');
        }

        $this->objectTypeProvider = $this->objectType->getProcessor();
        $this->likeableObject = $this->objectTypeProvider->getObjectByID($this->parameters['data']['objectID']);
        $this->likeableObject->setObjectType($this->objectType);
        if ($this->objectTypeProvider instanceof IRestrictedLikeObjectTypeProvider) {
            if (!$this->objectTypeProvider->canViewLikes($this->likeableObject)) {
                throw new PermissionDeniedException();
            }
        } elseif (!$this->objectTypeProvider->checkPermissions($this->likeableObject)) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function validateGetGroupedUserList()
    {
        $this->validateObjectParameters();

        $this->readInteger('pageNo');

        if ($this->parameters['pageNo'] < 1) {
            throw new UserInputException('pageNo');
        }
    }

    /**
     * @inheritDoc
     */
    public function getGroupedUserList()
    {
        // fetch number of pages
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_like
                WHERE   objectID = ?
                    AND objectTypeID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->parameters['data']['objectID'],
            $this->objectType->objectTypeID,
        ]);
        $pageCount = \ceil($statement->fetchSingleColumn() / 20);

        $sql = "SELECT      userID, likeValue
                FROM        wcf1_like
                WHERE       objectID = ?
                        AND objectTypeID = ?
                ORDER BY    likeValue DESC, time DESC";
        $statement = WCF::getDB()->prepare($sql, 20, ($this->parameters['pageNo'] - 1) * 20);
        $statement->execute([
            $this->parameters['data']['objectID'],
            $this->objectType->objectTypeID,
        ]);
        $data = [
            Like::LIKE => [],
            Like::DISLIKE => [],
        ];
        while ($row = $statement->fetchArray()) {
            $data[$row['likeValue']][] = $row['userID'];
        }

        $values = [];
        if (!empty($data[Like::LIKE])) {
            $values[Like::LIKE] = new GroupedUserList(WCF::getLanguage()->get('wcf.like.details.like'));
            /** @noinspection PhpUndefinedMethodInspection */
            $values[Like::LIKE]->addUserIDs($data[Like::LIKE]);
        }
        if (!empty($data[Like::DISLIKE])) {
            $values[Like::DISLIKE] = new GroupedUserList(WCF::getLanguage()->get('wcf.like.details.dislike'));
            /** @noinspection PhpUndefinedMethodInspection */
            $values[Like::DISLIKE]->addUserIDs($data[Like::DISLIKE]);
        }

        // load user profiles
        GroupedUserList::loadUsers();

        WCF::getTPL()->assign([
            'groupedUsers' => $values,
        ]);

        return [
            'containerID' => $this->parameters['data']['containerID'],
            'pageCount' => $pageCount,
            'template' => WCF::getTPL()->fetch('groupedUserList'),
        ];
    }

    /**
     * Validates parameters to load likes.
     */
    public function validateLoad()
    {
        if (!MODULE_LIKE) {
            throw new IllegalLinkException();
        }

        $this->readInteger('lastLikeTime', true);
        $this->readInteger('userID');
        $this->readInteger('likeValue');
        $this->readString('likeType');

        $user = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['userID']);

        if ($user === null) {
            throw new IllegalLinkException();
        }

        if ($user->isProtected()) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * Loads a list of likes.
     *
     * @return  array
     */
    public function load()
    {
        $likeList = new ViewableLikeList();
        if ($this->parameters['lastLikeTime']) {
            $likeList->getConditionBuilder()->add("like_table.time < ?", [$this->parameters['lastLikeTime']]);
        }
        if ($this->parameters['likeType'] == 'received') {
            $likeList->getConditionBuilder()->add("like_table.objectUserID = ?", [$this->parameters['userID']]);
        } else {
            $likeList->getConditionBuilder()->add("like_table.userID = ?", [$this->parameters['userID']]);
        }
        $likeList->getConditionBuilder()->add("like_table.likeValue = ?", [$this->parameters['likeValue']]);
        $likeList->readObjects();
        if (!\count($likeList)) {
            return [];
        }

        // parse template
        WCF::getTPL()->assign([
            'likeList' => $likeList,
        ]);

        return [
            'lastLikeTime' => $likeList->getLastLikeTime(),
            'template' => WCF::getTPL()->fetch('userProfileLikeItem'),
        ];
    }

    /**
     * Copies likes from one object id to another.
     */
    public function copy()
    {
        $reactionAction = new ReactionAction([], 'copy', $this->getParameters());
        $reactionAction->executeAction();
    }
}
