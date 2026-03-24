<?php

namespace wcf\page;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\WCF;

/**
 * Shows a list with outstanding notifications of the active user.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends MultipleLinkPage<DatabaseObjectList<DatabaseObject>>
 */
class NotificationListPage extends MultipleLinkPage
{
    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * list of outstanding notifications
     * @var mixed[][]
     */
    public $notifications = [];

    #[\Override]
    public function countItems()
    {
        return UserNotificationHandler::getInstance()->countAllNotifications();
    }

    #[\Override]
    protected function initObjectList() {}

    #[\Override]
    protected function readObjects() {}

    #[\Override]
    public function readData()
    {
        parent::readData();

        $this->notifications = UserNotificationHandler::getInstance()->getNotifications(
            $this->sqlLimit,
            $this->sqlOffset,
            true
        );
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'notifications' => $this->notifications,
        ]);
    }
}
