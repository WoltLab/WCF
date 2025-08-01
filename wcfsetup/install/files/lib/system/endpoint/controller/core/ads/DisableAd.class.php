<?php

namespace wcf\system\endpoint\controller\core\ads;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\ad\Ad;
use wcf\data\ad\AdAction;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Disables the blog entry with the given ID.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
#[PostRequest("/core/ads/{id:\d+}/disable")]
final class DisableAd implements IController
{
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $ad = Helper::fetchObjectFromRequestParameter($variables['id'], Ad::class);

        $this->assertAdCanBeDisabled($ad);

        (new AdAction([$ad], 'toggle'))->executeAction();

        return new JsonResponse([]);
    }

    private function assertAdCanBeDisabled(Ad $ad): void
    {
        if (!\MODULE_WCF_AD) {
            throw new IllegalLinkException();
        }

        WCF::getSession()->checkPermissions(['admin.ad.canManageAd']);

        if ($ad->isDisabled) {
            throw new PermissionDeniedException();
        }
    }
}
