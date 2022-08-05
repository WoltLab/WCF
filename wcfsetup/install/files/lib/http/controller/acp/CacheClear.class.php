<?php

namespace wcf\http\controller\acp;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\acp\page\CacheListPage;
use wcf\system\cache\command\ClearCache;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Clears the cache.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Controller\Acp
 */
final class CacheClear implements RequestHandlerInterface
{
    private LinkHandler $linkHandler;

    public function __construct()
    {
        $this->linkHandler = LinkHandler::getInstance();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        WCF::getSession()->checkPermissions([
            'admin.management.canViewLog',
        ]);

        $command = new ClearCache();
        $command();

        if (isset($request->getParsedBody()['noRedirect'])) {
            return new EmptyResponse();
        } else {
            return new RedirectResponse(
                $this->linkHandler->getControllerLink(CacheListPage::class)
            );
        }
    }
}
