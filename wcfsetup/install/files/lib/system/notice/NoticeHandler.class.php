<?php

namespace wcf\system\notice;

use wcf\data\notice\Notice;
use wcf\system\cache\builder\NoticeCacheBuilder;
use wcf\system\object\filter\builder\NoticeObjectFilterBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles notice-related matters.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NoticeHandler extends SingletonFactory
{
    /**
     * list with all enabled notices
     * @var Notice[]
     */
    protected $notices = [];

    /**
     * suppresses display of notices
     * @var bool
     */
    protected static $disableNotices = false;

    #[\Override]
    protected function init()
    {
        $this->notices = NoticeCacheBuilder::getInstance()->getData();
    }

    /**
     * Returns the notices which are visible for the active user.
     *
     * @return  Notice[]
     */
    public function getVisibleNotices()
    {
        if (self::$disableNotices) {
            return [];
        }

        $filterBuilder = new NoticeObjectFilterBuilder();

        $notices = [];
        foreach ($this->notices as $notice) {
            if ($notice->isDismissed()) {
                continue;
            }

            if (!$filterBuilder->testUser($notice, WCF::getUser())) {
                continue;
            }

            $notices[$notice->noticeID] = $notice;
        }

        return $notices;
    }

    /**
     * Disables the display of notices for the active page.
     *
     * @return void
     */
    public static function disableNotices()
    {
        self::$disableNotices = true;
    }
}
