<?php

namespace wcf\page;

use wcf\data\user\activity\event\ViewableUserActivityEventList;
use wcf\data\user\ignore\UserIgnore;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\UserProfileHandler;
use wcf\system\WCF;

/**
 * Shows the global recent activity list page.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class RecentActivityListPage extends AbstractPage
{
    /**
     * viewable user activity event list
     * @var ?ViewableUserActivityEventList
     */
    public $eventList;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->canonicalURL = LinkHandler::getInstance()->getControllerLink(RecentActivityListPage::class);
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        $this->eventList = new ViewableUserActivityEventList();

        if (UserProfileHandler::getInstance()->getIgnoredUsers(UserIgnore::TYPE_HIDE_MESSAGES) !== []) {
            $this->eventList->getConditionBuilder()->add(
                "user_activity_event.userID NOT IN (?)",
                [UserProfileHandler::getInstance()->getIgnoredUsers(UserIgnore::TYPE_HIDE_MESSAGES)]
            );
        }

        // load more items than necessary to avoid empty list if some items are invisible for current user
        $this->eventList->sqlLimit = 60;

        $this->eventList->readObjects();

        // add breadcrumbs
        if (\MODULE_MEMBERS_LIST !== 0) {
            PageLocationManager::getInstance()->addParentLocation('com.woltlab.wcf.MembersList');
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        // removes orphaned and non-accessible events
        UserActivityEventHandler::validateEvents($this->eventList);

        // remove unused items
        $this->eventList->truncate(20);

        WCF::getTPL()->assign([
            'eventList' => $this->eventList,
            'lastEventTime' => $this->eventList->getLastEventTime(),
        ]);
    }
}
