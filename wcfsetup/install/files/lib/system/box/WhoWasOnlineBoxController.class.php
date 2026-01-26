<?php

namespace wcf\system\box;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\user\online\UserOnline;
use wcf\data\user\online\UsersOnlineList;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\cache\tolerant\WhoWasOnlineCache;
use wcf\system\event\EventHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Box controller for a list of registered users that visited the website in last 24 hours.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @extends AbstractDatabaseObjectListBoxController<DatabaseObjectList<DatabaseObject>>
 */
class WhoWasOnlineBoxController extends AbstractDatabaseObjectListBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['footerBoxes', 'sidebarLeft', 'sidebarRight'];

    /**
     * @inheritDoc
     */
    protected $sortFieldLanguageItemPrefix = 'wcf.user.sortField';

    /**
     * @inheritDoc
     */
    public $validSortFields = [
        'username',
        'lastActivityTime',
    ];

    /**
     * users loaded from cache
     * @var UserProfile[]
     */
    public $users = [];

    /**
     * @inheritDoc
     */
    protected function getObjectList()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function getTemplate()
    {
        return WCF::getTPL()->render('wcf', 'boxWhoWasOnline', [
            'whoWasOnlineList' => $this->users,
            'whoWasOnlineTimeFormat' => WCF::getLanguage()->get(DateUtil::TIME_FORMAT),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function hasContent()
    {
        if (!MODULE_USERS_ONLINE || !WCF::getSession()->getPermission('user.profile.canViewUsersOnlineList')) {
            return false;
        }

        parent::hasContent();

        return \count($this->users) > 0;
    }

    /**
     * @inheritDoc
     */
    protected function loadContent()
    {
        $this->readObjects();

        $this->content = $this->getTemplate();
    }

    /**
     * @inheritDoc
     */
    protected function readObjects()
    {
        EventHandler::getInstance()->fireAction($this, 'readObjects');

        $userIDs = (new WhoWasOnlineCache())->getCache();

        if (!empty($userIDs)) {
            $this->users = \array_filter(
                UserProfileRuntimeCache::getInstance()->getObjects($userIDs),
                static function ($user) {
                    return $user !== null;
                }
            );
            foreach ($this->users as $key => $user) {
                // remove invisible users
                if (!UsersOnlineList::isVisibleUser(new UserOnline($user->getDecoratedObject()))) {
                    unset($this->users[$key]);
                }
            }

            // sort users
            if (!empty($this->users)) {
                DatabaseObject::sort($this->users, $this->sortField, $this->sortOrder);
            }
        }
    }
}
