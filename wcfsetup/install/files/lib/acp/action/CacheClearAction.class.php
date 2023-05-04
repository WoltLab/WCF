<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use wcf\acp\page\CacheListPage;
use wcf\action\AbstractSecureAction;
use wcf\system\cache\command\ClearCache;
use wcf\system\request\LinkHandler;

/**
 * Clears the cache.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CacheClearAction extends AbstractSecureAction
{
    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canRebuildData'];

    /**
     * @inheritDoc
     */
    public function execute()
    {
        parent::execute();

        $command = new ClearCache();
        $command();

        $this->executed();

        if (isset($_POST['noRedirect'])) {
            return new EmptyResponse();
        } else {
            return new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(CacheListPage::class)
            );
        }
    }
}
