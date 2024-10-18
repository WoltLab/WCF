<?php

namespace wcf\data\user\trophy;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\user\UserAction;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileAction;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\UserTrophyNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;

/**
 * Provides user trophy actions.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @method  UserTrophyEditor[]      getObjects()
 * @method  UserTrophyEditor        getSingleObject()
 */
class UserTrophyAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.trophy.canAwardTrophy'];

    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getGroupedUserTrophyList'];

    /**
     * @var UserProfile
     */
    public $userProfile;

    /**
     * @inheritDoc
     */
    public function create()
    {
        /** @var UserTrophy $userTrophy */
        $userTrophy = parent::create();

        if (!$userTrophy->getTrophy()->isDisabled()) {
            $userAction = new UserAction([$userTrophy->userID], 'update', [
                'counters' => [
                    'trophyPoints' => 1,
                ],
            ]);
            $userAction->executeAction();

            // checks if the user still has space to add special trophies
            if (\count($userTrophy->getUserProfile()->getSpecialTrophies()) < $userTrophy->getUserProfile()->getPermission('user.profile.trophy.maxUserSpecialTrophies')) {
                $hasTrophy = false;
                foreach (UserTrophyList::getUserTrophies([$userTrophy->getUserProfile()->userID])[$userTrophy->getUserProfile()->userID] as $trophy) {
                    if ($trophy->trophyID == $userTrophy->trophyID && $trophy->userTrophyID !== $userTrophy->userTrophyID) {
                        $hasTrophy = true;
                        break;
                    }
                }

                if (!$hasTrophy) {
                    $userProfileAction = new UserProfileAction(
                        [$userTrophy->getUserProfile()->getDecoratedObject()],
                        'updateSpecialTrophies',
                        [
                            'trophyIDs' => \array_unique(\array_merge(\array_map(static function ($trophy) {
                                return $trophy->trophyID;
                            }, $userTrophy->getUserProfile()->getSpecialTrophies()), [$userTrophy->trophyID])),
                        ]
                    );
                    $userProfileAction->executeAction();
                }
            }
        }

        UserActivityEventHandler::getInstance()->fireEvent(
            'com.woltlab.wcf.userTrophy.recentActivityEvent.trophyReceived',
            $userTrophy->getObjectID(),
            null,
            $userTrophy->userID
        );

        if ($userTrophy->userID != WCF::getUser()->userID) {
            UserNotificationHandler::getInstance()->fireEvent(
                'received',
                'com.woltlab.wcf.userTrophy.notification',
                new UserTrophyNotificationObject($userTrophy),
                [
                    $userTrophy->userID,
                ]
            );
        }

        return $userTrophy;
    }

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        parent::validateDelete();

        /** @var UserTrophy $object */
        foreach ($this->objects as $object) {
            if ($object->getTrophy()->awardAutomatically) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        if (empty($this->objects)) {
            $this->readObjects();
        }

        $trophyIDs = $userIDs = [];
        foreach ($this->getObjects() as $object) {
            $trophyIDs[] = $object->trophyID;
            $userIDs[] = $object->userID;
        }

        $returnValues = parent::delete();

        if (!empty($this->objects)) {
            // update user special trophies trophies
            $userTrophies = UserTrophyList::getUserTrophies($userIDs);

            foreach ($userTrophies as $userID => $trophies) {
                $userTrophyIDs = [];
                foreach ($trophies as $trophy) {
                    $userTrophyIDs[] = $trophy->trophyID;
                }

                $conditionBuilder = new PreparedStatementConditionBuilder();
                if (!empty($userTrophyIDs)) {
                    $conditionBuilder->add('trophyID NOT IN (?)', [\array_unique($userTrophyIDs)]);
                }
                $conditionBuilder->add('userID = ?', [$userID]);

                $sql = "DELETE FROM wcf1_user_special_trophy
                        " . $conditionBuilder;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditionBuilder->getParameters());

                UserStorageHandler::getInstance()->reset([$userID], 'specialTrophies');
            }

            $updateUserTrophies = [];
            foreach ($this->getObjects() as $object) {
                if (!$object->getTrophy()->isDisabled()) {
                    if (!isset($updateUserTrophies[$object->userID])) {
                        $updateUserTrophies[$object->userID] = 0;
                    }
                    $updateUserTrophies[$object->userID]--;
                }
            }

            foreach ($updateUserTrophies as $userID => $count) {
                $userAction = new UserAction([$userID], 'update', [
                    'counters' => [
                        'trophyPoints' => $count,
                    ],
                ]);
                $userAction->executeAction();
            }
        }

        return $returnValues;
    }

    /**
     * Validates the getGroupedUserTrophyList method.
     */
    public function validateGetGroupedUserTrophyList()
    {
        if (!MODULE_TROPHY) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['user.profile.trophy.canSeeTrophies']);

        $this->readInteger('pageNo');
        $this->readInteger('userID');

        $this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->parameters['userID']);
        if (!$this->userProfile) {
            throw new UserInputException('userID');
        }
        if (!$this->userProfile->isAccessible('canViewTrophies') && $this->userProfile->userID != WCF::getSession()->userID) {
            throw new PermissionDeniedException();
        }

        if ($this->parameters['pageNo'] < 1) {
            throw new UserInputException('pageNo');
        }
    }

    /**
     * Returns a viewable user trophy list for a specific user.
     */
    public function getGroupedUserTrophyList()
    {
        $userTrophyList = new UserTrophyList();
        $userTrophyList->getConditionBuilder()->add('userID = ?', [$this->parameters['userID']]);
        if (!empty($userTrophyList->sqlJoins)) {
            $userTrophyList->sqlJoins .= ' ';
        }
        if (!empty($userTrophyList->sqlConditionJoins)) {
            $userTrophyList->sqlConditionJoins .= ' ';
        }
        $userTrophyList->sqlJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID';
        $userTrophyList->sqlConditionJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID';

        // trophy category join
        $userTrophyList->sqlJoins .= '
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';
        $userTrophyList->sqlConditionJoins .= '
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';

        $userTrophyList->getConditionBuilder()->add('trophy.isDisabled = ?', [0]);
        $userTrophyList->getConditionBuilder()->add('category.isDisabled = ?', [0]);
        $userTrophyList->sqlLimit = 10;
        $userTrophyList->sqlOffset = ($this->parameters['pageNo'] - 1) * 10;
        $userTrophyList->sqlOrderBy = 'time DESC';
        $pageCount = \ceil($userTrophyList->countObjects() / 10);
        $userTrophyList->readObjects();

        return [
            'pageCount' => $pageCount,
            'title' => WCF::getLanguage()->getDynamicVariable(
                'wcf.user.trophy.dialogTitle',
                ['username' => $this->userProfile->username]
            ),
            'template' => WCF::getTPL()->fetch('groupedUserTrophyList', 'wcf', [
                'userTrophyList' => $userTrophyList,
            ]),
        ];
    }
}
