<?php

namespace wcf\system\endpoint\controller\core\packages\updates;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\acp\page\PackageUpdatePage;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\UserInputException;
use wcf\system\package\PackageUpdateDispatcher;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Refreshes the package database and returns the link to the update page if
 * there are any updates available.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/packages/updates/search')]
final class SearchForUpdates implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertCanSearchForUpdates();

        PackageUpdateDispatcher::getInstance()->refreshPackageDatabase([], true);

        $url = '';
        if (PackageUpdateDispatcher::getInstance()->getAvailableUpdates() !== []) {
            $url = LinkHandler::getInstance()->getControllerLink(PackageUpdatePage::class);
        }

        return new JsonResponse([
            'url' => $url,
        ]);
    }

    private function assertCanSearchForUpdates(): void
    {
        WCF::getSession()->checkPermissions(['admin.configuration.package.canUpdatePackage']);

        if (\ENABLE_BENCHMARK !== 0) {
            throw new UserInputException(
                'benchmark',
                WCF::getLanguage()->getDynamicVariable('wcf.acp.package.searchForUpdates.benchmark')
            );
        }
    }
}
