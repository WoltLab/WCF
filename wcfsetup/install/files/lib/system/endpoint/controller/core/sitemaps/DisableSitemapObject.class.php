<?php

namespace wcf\system\endpoint\controller\core\sitemaps;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\sitemap\SitemapHandler;
use wcf\system\WCF;

/**
 * Disables the sitemap object with the given name.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/sitemaps/{id}/disable')]
final class DisableSitemapObject implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        WCF::getSession()->checkPermissions(['admin.management.canRebuildData']);

        $sitemapObject = SitemapHandler::getInstance()->getObject($variables['id']);
        if ($sitemapObject === null) {
            throw new IllegalLinkException();
        }

        SitemapHandler::getInstance()->setIsDisabled($sitemapObject, true);

        return new JsonResponse([]);
    }
}
