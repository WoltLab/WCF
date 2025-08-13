<?php

namespace wcf\system\endpoint\controller\core\ads;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\ad\Ad;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Enables the blog entry with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest("/core/ads/{id:\d+}/enable")]
class EnableAd implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $ad = Helper::fetchObjectFromRequestParameter($variables['id'], Ad::class);

        $this->assertAdCanBeEnabled();

        if ($ad->isDisabled) {
            (new \wcf\command\ad\EnableAd($ad))();
        }

        return new JsonResponse([]);
    }

    private function assertAdCanBeEnabled(): void
    {
        if (!\MODULE_WCF_AD) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.ad.canManageAd']);
    }
}
