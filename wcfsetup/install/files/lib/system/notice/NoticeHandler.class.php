<?php

namespace wcf\system\notice;

use wcf\data\notice\Notice;
use wcf\system\cache\eager\NoticeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\condition\provider\combined\NoticeConditionProvider;
use wcf\system\condition\type\IGlobalConditionType;
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

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->notices = (new NoticeCache())->getCache();
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

        $notices = [];
        $provider = new NoticeConditionProvider();
        foreach ($this->notices as $notice) {
            if ($notice->isDismissed()) {
                continue;
            }

            $conditions = ConditionHandler::getInstance()->getConditionsWithFilter($provider, $notice->getConditions());
            foreach ($conditions as $condition) {
                $matches = $condition instanceof IGlobalConditionType
                    ? $condition->matches()
                    : $condition->matches(WCF::getUser());

                if (!$matches) {
                    continue 2;
                }
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
