<?php

namespace wcf\system\user;

use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\WCF;

/**
 * Provides a grouped list of users.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class GroupedUserList implements \Countable, \Iterator
{
    /**
     * list of user profiles shared across all instances of GroupedUserList
     * @var UserProfile[]
     */
    protected static array $users = [];

    /**
     * group name
     */
    protected string $groupName = '';

    /**
     * current iterator index
     */
    protected int $index = 0;

    /**
     * message displayed if no users are in this group
     */
    protected string $noUsersMessage = '';

    /**
     * list of user ids assigned for this group
     * @var int[]
     */
    protected array $userIDs = [];

    /**
     * Creates a new grouped list of users.
     */
    public function __construct(string $groupName = '', string $noUsersMessage = '')
    {
        $this->groupName = $groupName;
        $this->noUsersMessage = $noUsersMessage;
    }

    /**
     * Returns the group name.
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * Returns the message if no users are in this group.
     */
    public function getNoUsersMessage(): string
    {
        return $this->noUsersMessage ? WCF::getLanguage()->get($this->noUsersMessage) : '';
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getGroupName();
    }

    /**
     * Adds a list of user ids to this group.
     *
     * @param int[] $userIDs
     */
    public function addUserIDs(array $userIDs): void
    {
        foreach ($userIDs as $userID) {
            // already added, ignore
            if (\in_array($userID, $this->userIDs)) {
                continue;
            }

            $this->userIDs[] = $userID;

            // add entry to static cache
            self::$users[$userID] = null;
        }
    }

    /**
     * Loads user profiles for outstanding user ids.
     */
    public static function loadUsers(): void
    {
        $userIDs = [];
        foreach (self::$users as $userID => $user) {
            if ($user === null) {
                $userIDs[] = $userID;
            }
        }

        // load user profiles
        if (!empty($userIDs)) {
            $userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
            foreach ($userProfiles as $userID => $userProfile) {
                if ($userProfile) {
                    self::$users[$userID] = $userProfile;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return \count($this->userIDs);
    }

    /**
     * @inheritDoc
     */
    public function current(): UserProfile
    {
        $userID = $this->userIDs[$this->index];

        return self::$users[$userID];
    }

    /**
     * CAUTION: This methods does not return the current iterator index,
     * rather than the object key which maps to that index.
     *
     * @see \Iterator::key()
     */
    public function key(): int
    {
        return $this->userIDs[$this->index];
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->userIDs[$this->index]);
    }
}
