<?php

namespace wcf\system\sitemap\object;

use wcf\data\page\PageCache;
use wcf\data\user\User;
use wcf\data\user\UserList;
use wcf\system\WCF;

/**
 * User sitemap implementation.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @extends AbstractSitemapObjectObjectType<User, UserList>
 */
class UserSitemapObject extends AbstractSitemapObjectObjectType
{
    #[\Override]
    public function getObjectClass()
    {
        return User::class;
    }

    #[\Override]
    public function getLastModifiedColumn()
    {
        return 'lastActivityTime';
    }

    #[\Override]
    public function isAvailableType()
    {
        if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
            return false;
        }

        return !!PageCache::getInstance()->getPageByIdentifier('com.woltlab.wcf.User')->allowSpidersToIndex;
    }
}
