<?php

namespace wcf\system\page\handler;

use wcf\page\UserArticleListPage;
use wcf\system\cache\runtime\UserRuntimeCache;
use wcf\system\request\LinkHandler;

/**
 * Page handler implementation for the page showing the articles created by a user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserArticleListPageHandler extends AbstractLookupPageHandler implements IOnlineLocationPageHandler
{
    use TUserLookupPageHandler;
    use TUserOnlineLocationPageHandler;

    #[\Override]
    public function getLink(int $objectID): string
    {
        return LinkHandler::getInstance()->getControllerLink(UserArticleListPage::class, [
            'object' => UserRuntimeCache::getInstance()->getObject($objectID),
        ]);
    }
}
