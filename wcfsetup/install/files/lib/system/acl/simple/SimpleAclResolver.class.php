<?php

namespace wcf\system\acl\simple;

use wcf\data\user\User;
use wcf\system\cache\builder\SimpleAclCacheBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Simplified ACL handlers that stores access data for objects requiring
 * just a simple yes/no instead of a set of different permissions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SimpleAclResolver extends SingletonFactory
{
    /**
     * cached permissions per object type
     * @var array<string, array<int, array{group: list<int>, user: list<int>}>>
     */
    protected $cache = [];

    /**
     * Returns true if there are no ACL settings, the user is allowed or
     * one of its group is allowed.
     *
     * @param ?User $user user object, if `null` uses current user
     * @return bool false if user is not allowed
     */
    public function canAccess(string $objectType, int $objectID, ?User $user = null)
    {
        if ($user === null) {
            $user = WCF::getUser();
        }

        $this->loadCache($objectType);

        // allow all
        if (!isset($this->cache[$objectType][$objectID])) {
            return true;
        }

        if ($user->userID) {
            // user is explicitly allowed
            if (\in_array($user->userID, $this->cache[$objectType][$objectID]['user'])) {
                return true;
            }
        }

        // check for user groups
        $groupIDs = $user->getGroupIDs();
        foreach ($groupIDs as $groupID) {
            // group is explicitly allowed
            if (\in_array($groupID, $this->cache[$objectType][$objectID]['group'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resets the cache for provided object type.
     *
     * @return void
     */
    public function resetCache(string $objectType)
    {
        SimpleAclCacheBuilder::getInstance()->reset(['objectType' => $objectType]);
    }

    /**
     * Attempts to load the cache for provided object type.
     *
     * @return void
     */
    protected function loadCache(string $objectType)
    {
        if (!isset($this->cache[$objectType])) {
            $this->cache[$objectType] = SimpleAclCacheBuilder::getInstance()->getData(['objectType' => $objectType]);
        }
    }
}
