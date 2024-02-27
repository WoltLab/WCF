<?php

namespace wcf\data\user\online;

use wcf\data\option\OptionAction;
use wcf\data\session\SessionList;
use wcf\data\user\group\UserGroup;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\system\event\EventHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a list of currently online users.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  UserOnline      current()
 * @method  UserOnline[]        getObjects()
 * @method  UserOnline|null         getSingleObject()
 * @method  UserOnline|null         search($objectID)
 * @property    UserOnline[] $objects
 */
class UsersOnlineList extends SessionList
{
    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'user_table.username';

    /**
     * users online stats
     * @var array
     */
    public $stats = [
        'total' => 0,
        'invisible' => 0,
        'members' => 0,
        'guests' => 0,
    ];

    /**
     * users online markings
     * @var array
     */
    public $usersOnlineMarkings;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->sqlSelects .= "user_avatar.*, user_option_value.*, user_group.userOnlineMarking, user_table.*";

        $this->sqlConditionJoins .= "
            LEFT JOIN   wcf" . WCF_N . "_user user_table
            ON          user_table.userID = session.userID";
        $this->sqlJoins .= "
            LEFT JOIN   wcf" . WCF_N . "_user user_table
            ON          user_table.userID = session.userID
            LEFT JOIN   wcf" . WCF_N . "_user_option_value user_option_value
            ON          user_option_value.userID = user_table.userID
            LEFT JOIN   wcf" . WCF_N . "_user_avatar user_avatar
            ON          user_avatar.avatarID = user_table.avatarID
            LEFT JOIN   wcf" . WCF_N . "_user_group user_group
            ON          user_group.groupID = user_table.userOnlineGroupID";

        $this->getConditionBuilder()->add('session.lastActivityTime > ?', [TIME_NOW - USER_ONLINE_TIMEOUT]);
    }

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        $objects = $this->objects;
        $this->indexToObject = $this->objects = [];

        foreach ($objects as $object) {
            $object = new UserOnline(new User(null, null, $object));
            if (!$object->userID || self::isVisibleUser($object)) {
                $this->objects[$object->sessionID] = $object;
                $this->indexToObject[] = $object->sessionID;
            }
        }
        $this->objectIDs = $this->indexToObject;
        $this->rewind();
    }

    /**
     * Fetches users online stats.
     */
    public function readStats()
    {
        $conditionBuilder = clone $this->getConditionBuilder();
        $conditionBuilder->add('session.spiderIdentifier IS NULL');

        $sql = "SELECT      user_option_value.userOption" . User::getUserOptionID('canViewOnlineStatus') . " AS canViewOnlineStatus, session.userID
                FROM        wcf" . WCF_N . "_session session
                LEFT JOIN   wcf" . WCF_N . "_user_option_value user_option_value
                ON          user_option_value.userID = session.userID
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());

        $users = $userIDs = [];
        while ($row = $statement->fetchArray()) {
            $this->stats['total']++;

            $user = new UserOnline(new User(null, $row));
            if ($user->userID) {
                $this->stats['members']++;
                $users[] = $user;
                $userIDs[] = $user->userID;
            } else {
                $this->stats['guests']++;
            }
        }

        foreach ($users as $user) {
            if ($user->canViewOnlineStatus && !self::isVisibleUser($user)) {
                $this->stats['invisible']++;
            }
        }
    }

    /**
     * Returns a list of the users online markings.
     *
     * @return  array
     */
    public function getUsersOnlineMarkings()
    {
        if ($this->usersOnlineMarkings === null) {
            $this->usersOnlineMarkings = $priorities = [];

            // get groups
            foreach (UserGroup::getGroupsByType() as $group) {
                if ($group->userOnlineMarking != '%s') {
                    $priorities[] = $group->priority;
                    $this->usersOnlineMarkings[] = \str_replace(
                        '%s',
                        StringUtil::encodeHTML(WCF::getLanguage()->get($group->groupName)),
                        $group->userOnlineMarking
                    );
                }
            }

            // sort list
            \array_multisort($priorities, \SORT_DESC, $this->usersOnlineMarkings);
        }

        return $this->usersOnlineMarkings;
    }

    /**
     * Checks the users online record.
     */
    public function checkRecord()
    {
        $usersOnlineTotal = (USERS_ONLINE_RECORD_NO_GUESTS ? $this->stats['members'] : $this->stats['total']);
        if ($usersOnlineTotal > USERS_ONLINE_RECORD) {
            // save new record
            $optionAction = new OptionAction([], 'import', [
                'data' => [
                    'users_online_record' => $usersOnlineTotal,
                    'users_online_record_time' => TIME_NOW,
                ],
            ]);
            $optionAction->executeAction();
        }
    }

    /**
     * Checks the 'canViewOnlineStatus' setting.
     *
     * @param int $userID
     * @param int $canViewOnlineStatus
     * @return  bool
     * @deprecated  5.3             Use `isVisibleUser` instead
     */
    public static function isVisible($userID, $canViewOnlineStatus)
    {
        if (WCF::getSession()->getPermission('admin.user.canViewInvisible') || $userID == WCF::getUser()->userID) {
            return true;
        }

        $data = [
            'result' => false,
            'userID' => $userID,
            'canViewOnlineStatus' => $canViewOnlineStatus,
        ];

        switch ($canViewOnlineStatus) {
            case UserProfile::ACCESS_EVERYONE:
                $data['result'] = true;
                break;

            case UserProfile::ACCESS_REGISTERED:
                if (WCF::getUser()->userID) {
                    $data['result'] = true;
                }
                break;

            case UserProfile::ACCESS_FOLLOWING:
                /** @noinspection PhpUndefinedMethodInspection */
                if (WCF::getUserProfileHandler()->isFollower($userID)) {
                    $data['result'] = true;
                }
                break;
        }

        EventHandler::getInstance()->fireAction(static::class, 'isVisible', $data);

        return $data['result'];
    }

    /**
     * Checks the 'canViewOnlineStatus' setting for the given user.
     *
     * @param UserOnline $userOnline
     * @return      bool
     * @since       5.3
     */
    public static function isVisibleUser(UserOnline $userOnline)
    {
        if (WCF::getSession()->getPermission('admin.user.canViewInvisible') || $userOnline->userID == WCF::getUser()->userID) {
            return true;
        }

        $data = [
            'result' => false,
            'userOnline' => $userOnline,
        ];

        switch ($userOnline->canViewOnlineStatus) {
            case UserProfile::ACCESS_EVERYONE:
                $data['result'] = true;
                break;

            case UserProfile::ACCESS_REGISTERED:
                if (WCF::getUser()->userID) {
                    $data['result'] = true;
                }
                break;

            case UserProfile::ACCESS_FOLLOWING:
                /** @noinspection PhpUndefinedMethodInspection */
                if (WCF::getUserProfileHandler()->isFollower($userOnline->userID)) {
                    $data['result'] = true;
                }
                break;
        }

        EventHandler::getInstance()->fireAction(static::class, 'isVisibleUser', $data);

        return $data['result'];
    }
}
