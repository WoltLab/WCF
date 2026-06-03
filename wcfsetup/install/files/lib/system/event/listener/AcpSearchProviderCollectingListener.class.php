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
        $event->register('com.woltlab.wcf.article', new ArticleACPSearchResultProvider());
        $event->register('com.woltlab.wcf.box', new BoxACPSearchResultProvider());
        $event->register('com.woltlab.wcf.menuItem', new MenuItemACPSearchResultProvider());
        $event->register('com.woltlab.wcf.option', new OptionACPSearchResultProvider());
        $event->register('com.woltlab.wcf.package', new PackageACPSearchResultProvider());
        $event->register('com.woltlab.wcf.page', new PageACPSearchResultProvider());
        $event->register('com.woltlab.wcf.trophy', new TrophyACPSearchResultProvider());
        $event->register('com.woltlab.wcf.user', new UserACPSearchResultProvider());
        $event->register('com.woltlab.wcf.userGroupOption', new UserGroupOptionACPSearchResultProvider());
    }
}
