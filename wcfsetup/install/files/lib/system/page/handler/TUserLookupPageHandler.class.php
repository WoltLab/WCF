<?php

namespace wcf\system\page\handler;

use wcf\data\user\UserProfileList;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\WCF;

/**
 * Provides the `isValid` and `lookup` methods for looking up users.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
trait TUserLookupPageHandler
{
    /**
     * Returns true if provided object id exists and is valid.
     *
     * @return  bool        true if object id is valid
     * @see ILookupPageHandler::isValid()
     */
    public function isValid(int $objectID)
    {
        return UserRuntimeCache::getInstance()->getObject($objectID) !== null;
    }

    /**
     * Performs a search for pages using a query string, returning an array containing
     * an `objectID => title` relation.
     *
     * @return list<array<string, int|string>>
     * @see ILookupPageHandler::lookup()
     */
    public function lookup(string $searchString)
    {
        $userList = new UserProfileList();
        $userList->getConditionBuilder()->add('user_table.username LIKE ?', ['%' . WCF::getDB()->escapeLikeValue($searchString) . '%']);
        $userList->readObjects();

        $results = [];
        foreach ($userList as $user) {
            $results[] = [
                'image' => $user->getAvatar()->getImageTag(48),
                'link' => $this->getLink($user->userID),
                'objectID' => $user->userID,
                'title' => $user->username,
            ];
        }

        return $results;
    }
}
