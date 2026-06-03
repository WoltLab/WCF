<?php

namespace wcf\system\event\listener;

use wcf\event\acp\search\provider\ProviderCollecting;
use wcf\system\search\acp\ArticleACPSearchResultProvider;
use wcf\system\search\acp\BoxACPSearchResultProvider;
use wcf\system\search\acp\MenuItemACPSearchResultProvider;
use wcf\system\search\acp\OptionACPSearchResultProvider;
use wcf\system\search\acp\PackageACPSearchResultProvider;
use wcf\system\search\acp\PageACPSearchResultProvider;
use wcf\system\search\acp\TrophyACPSearchResultProvider;
use wcf\system\search\acp\UserACPSearchResultProvider;
use wcf\system\search\acp\UserGroupOptionACPSearchResultProvider;

/**
 * Registers the built-in ACP search providers of the core.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class AcpSearchProviderCollectingListener
{
    public function __invoke(ProviderCollecting $event): void
    {
        $event->register('com.woltlab.wcf.menuItem', new MenuItemACPSearchResultProvider(), 1);
        $event->register('com.woltlab.wcf.option', new OptionACPSearchResultProvider(), 2);
        $event->register('com.woltlab.wcf.user', new UserACPSearchResultProvider(), 3);
        $event->register('com.woltlab.wcf.userGroupOption', new UserGroupOptionACPSearchResultProvider(), 4);
        $event->register('com.woltlab.wcf.package', new PackageACPSearchResultProvider(), 5);
        $event->register('com.woltlab.wcf.page', new PageACPSearchResultProvider(), 6);
        $event->register('com.woltlab.wcf.box', new BoxACPSearchResultProvider(), 7);
        $event->register('com.woltlab.wcf.article', new ArticleACPSearchResultProvider(), 8);
        $event->register('com.woltlab.wcf.trophy', new TrophyACPSearchResultProvider(), 9);
    }
}
