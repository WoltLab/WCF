<?php

namespace wcf\system\box;

use wcf\system\cache\tolerant\UserStatsCache;
use wcf\system\WCF;

/**
 * Box that shows global statistics.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class StatisticsBoxController extends AbstractBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = ['sidebarLeft', 'sidebarRight'];

    #[\Override]
    protected function loadContent()
    {
        if (WCF::getSession()->hasPermission('user.profile.canViewStatistics')) {
            $this->content = WCF::getTPL()->render(
                'wcf',
                'boxStatistics',
                ['statistics' => (new UserStatsCache())->getCache()]
            );
        }
    }
}
